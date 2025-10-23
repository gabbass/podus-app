<?php

namespace App\Services;

use ExamOmrConfig;
use LegacyConfig;
use PDO;
use RuntimeException;

class ExamAutoGrader
{
    private PDO $pdo;
    /** @var callable|null */
    private $httpHandler;

    public function __construct(?PDO $pdo = null, ?callable $httpHandler = null)
    {
        $this->pdo = $pdo ?? LegacyConfig::createPdo();
        $this->httpHandler = $httpHandler;
    }

    /**
     * @param array<string,mixed> $scan
     * @return array<string,mixed>
     */
    public function grade(array $scan): array
    {
        $response = $this->sendToProvider($scan);
        $normalized = $this->normalizeResponse($response);
        $legacy = $this->translateToLegacy($scan, $normalized);

        return [
            'provider_scan_id' => $response['scan_id'] ?? null,
            'overall_confidence' => $normalized['overall_confidence'],
            'requires_review' => $legacy['requires_review'],
            'items' => $normalized['items'],
            'legacy' => $legacy,
            'raw' => $response,
        ];
    }

    /**
     * @param array<string,mixed> $scan
     * @return array<string,mixed>
     */
    private function sendToProvider(array $scan): array
    {
        if ($this->httpHandler !== null) {
            $handler = $this->httpHandler;
            $result = $handler($scan);
            if (!is_array($result)) {
                throw new RuntimeException('Resposta inválida do handler OMR.');
            }

            return $result;
        }

        $config = ExamOmrConfig::providerConfig();
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        if ($baseUrl === '') {
            throw new RuntimeException('Endpoint do serviço OMR não configurado.');
        }

        $url = $baseUrl . '/scans';
        $filePath = $scan['file_path'] ?? null;
        if (!is_string($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('Arquivo de prova indisponível para envio ao provedor OMR.');
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Não foi possível inicializar a requisição OMR.');
        }

        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        $curlFile = new \CURLFile($filePath, $mime, basename($filePath));
        $payload = [
            'file' => $curlFile,
            'exam_id' => $scan['provas_online_id'] ?? null,
            'student_id' => $scan['student_matricula'] ?? null,
            'attempt' => $scan['attempt'] ?? 1,
        ];

        $headers = ['Accept: application/json'];
        $token = $config['api_key'] ?? $config['token'] ?? null;
        if (is_string($token) && $token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => (int) ($config['timeout'] ?? 30),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $responseBody = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            throw new RuntimeException($error !== '' ? $error : 'Falha desconhecida ao comunicar com o serviço OMR.');
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(sprintf('O provedor OMR retornou HTTP %d.', $status));
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Resposta inválida do serviço OMR.');
        }

        return $decoded;
    }

    /**
     * @param array<string,mixed> $response
     * @return array<string,mixed>
     */
    private function normalizeResponse(array $response): array
    {
        $status = strtolower((string) ($response['status'] ?? 'completed'));
        if ($status !== 'completed') {
            throw new RuntimeException('O processamento do OMR não foi concluído.');
        }

        $answers = $response['answers'] ?? [];
        if (!is_array($answers)) {
            $answers = [];
        }

        $minConfidence = ExamOmrConfig::minConfidence();
        $reviewThreshold = ExamOmrConfig::reviewThreshold();

        $items = [];
        $confidences = [];
        $requiresReview = false;

        foreach ($answers as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $questionId = isset($entry['question_id']) ? (int) $entry['question_id'] : 0;
            if ($questionId === 0) {
                continue;
            }

            $choice = $this->sanitizeChoice($entry['choice'] ?? null);
            $confidence = $entry['confidence'] ?? null;
            $confidence = is_numeric($confidence) ? (float) $confidence : null;
            $itemStatus = $entry['status'] ?? 'detected';
            if (!is_string($itemStatus) || $itemStatus === '') {
                $itemStatus = 'detected';
            }
            $itemStatus = strtolower($itemStatus);

            if ($confidence !== null) {
                $confidences[] = $confidence;
                if ($confidence < $reviewThreshold) {
                    $requiresReview = true;
                }
            }

            if ($itemStatus !== 'detected' && $itemStatus !== 'blank') {
                $requiresReview = true;
            }

            $items[] = [
                'question_id' => $questionId,
                'detected_alternative' => $choice,
                'confidence' => $confidence,
                'status' => $confidence !== null && $confidence < $minConfidence ? 'low_confidence' : $itemStatus,
                'raw' => $entry,
            ];
        }

        $overall = $response['confidence'] ?? null;
        $overall = is_numeric($overall) ? (float) $overall : null;
        if ($overall === null && $confidences !== []) {
            $overall = array_sum($confidences) / count($confidences);
        }

        if ($overall !== null && $overall < $reviewThreshold) {
            $requiresReview = true;
        }

        return [
            'provider_scan_id' => $response['scan_id'] ?? null,
            'overall_confidence' => $overall,
            'requires_review' => $requiresReview,
            'items' => $items,
        ];
    }

    /**
     * @param array<string,mixed> $scan
     * @param array<string,mixed> $normalized
     * @return array<string,mixed>
     */
    private function translateToLegacy(array $scan, array $normalized): array
    {
        $examId = (int) ($scan['provas_online_id'] ?? 0);
        if ($examId <= 0) {
            throw new RuntimeException('Identificador da prova ausente no processamento.');
        }

        $provaOnline = $this->fetchProvaOnline($examId);
        if ($provaOnline === null) {
            throw new RuntimeException('Prova não encontrada para consolidação.');
        }

        $expected = $this->extractQuestionOrder($provaOnline);
        $items = $normalized['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $itemMap = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $questionId = isset($item['question_id']) ? (int) $item['question_id'] : 0;
            if ($questionId === 0) {
                continue;
            }
            $itemMap[$questionId] = $item;
        }

        if ($expected === [] && $itemMap !== []) {
            $expected = array_map('intval', array_keys($itemMap));
        }

        $questionData = $this->loadQuestions($expected);
        $minConfidence = ExamOmrConfig::minConfidence();
        $reviewThreshold = ExamOmrConfig::reviewThreshold();

        $answers = [];
        $correct = 0;
        $total = count($expected);
        $requiresReview = !empty($normalized['requires_review']);

        foreach ($expected as $questionId) {
            $question = $questionData[$questionId] ?? null;
            $gabarito = $question['resposta'] ?? null;
            if (is_string($gabarito)) {
                $gabarito = $this->sanitizeChoice($gabarito);
            }

            $detected = null;
            $confidence = null;
            $status = 'missing';

            if (isset($itemMap[$questionId])) {
                $entry = $itemMap[$questionId];
                $status = $entry['status'] ?? 'detected';
                if (!is_string($status) || $status === '') {
                    $status = 'detected';
                }
                $confidence = isset($entry['confidence']) && is_numeric($entry['confidence']) ? (float) $entry['confidence'] : null;
                $detected = $entry['detected_alternative'] ?? null;
                $detected = $this->sanitizeChoice($detected);

                if ($confidence !== null && $confidence < $minConfidence) {
                    $status = 'low_confidence';
                    $detected = null;
                }

                if ($confidence !== null && $confidence < $reviewThreshold) {
                    $requiresReview = true;
                }

                if ($status !== 'detected' && $status !== 'blank') {
                    $requiresReview = true;
                }
            }

            $isCorrect = $detected !== null && $gabarito !== null && strtoupper($detected) === strtoupper($gabarito);
            if ($isCorrect) {
                $correct++;
            }

            $answers[] = [
                'question_id' => $questionId,
                'answer' => $detected,
                'gabarito' => $gabarito,
                'confidence' => $confidence,
                'status' => $status,
                'correct' => $isCorrect,
            ];
        }

        $grade = $total > 0 ? round($correct / $total * 10, 2) : 0.0;

        return [
            'answers' => $answers,
            'summary' => [
                'total_questions' => $total,
                'correct' => $correct,
                'grade' => $grade,
            ],
            'requires_review' => $requiresReview,
        ];
    }

    /**
     * @param array<string,mixed> $provaOnline
     * @return array<int,int>
     */
    private function extractQuestionOrder(array $provaOnline): array
    {
        $ids = [];
        if (!empty($provaOnline['lista_quest'])) {
            $raw = explode(',', (string) $provaOnline['lista_quest']);
            foreach ($raw as $value) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
                $ids[] = (int) $value;
            }
        } elseif (!empty($provaOnline['id_questao'])) {
            $ids[] = (int) $provaOnline['id_questao'];
        }

        return array_values(array_filter(array_unique($ids)));
    }

    /**
     * @param array<int,int> $questionIds
     * @return array<int,array<string,mixed>>
     */
    private function loadQuestions(array $questionIds): array
    {
        if ($questionIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmt = $this->pdo->prepare('SELECT id, resposta, alternativa_A, alternativa_B, alternativa_C, alternativa_D, alternativa_E FROM questoes WHERE id IN (' . $placeholders . ')');
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível carregar questões da prova.');
        }

        $stmt->execute($questionIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $map = [];
        foreach ($rows as $row) {
            if (!isset($row['id'])) {
                continue;
            }
            $map[(int) $row['id']] = $row;
        }

        return $map;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function fetchProvaOnline(int $examId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM provas_online WHERE id = :id LIMIT 1');
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível consultar prova online.');
        }

        $stmt->execute([':id' => $examId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return is_array($row) ? $row : null;
    }

    private function sanitizeChoice($choice): ?string
    {
        if (is_string($choice)) {
            $choice = strtoupper(trim($choice));
            if ($choice !== '' && preg_match('/^[A-E]$/', $choice) === 1) {
                return $choice;
            }
        }

        return null;
    }
}
