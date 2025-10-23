<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Jobs\ProcessExamScan;
use App\Models\ExamScan;
use App\Models\ExamScanItem;
use DateTimeImmutable;
use ExamOmrConfig;
use LegacyConfig;
use PDO;
use RuntimeException;
use Throwable;

class ExamCorrectionController extends Controller
{
    public function __invoke(Request $request)
    {
        $action = $request->input('acao');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && $action === null) {
            $action = 'upload';
        }

        return match ($action) {
            'upload' => $this->upload($request),
            'attempts' => $this->attempts($request),
            'history' => $this->attempts($request),
            default => $this->status($request),
        };
    }

    protected function upload(Request $request)
    {
        $examId = (int) $request->input('exam_id');
        $matricula = trim((string) $request->input('matricula'));

        if ($examId <= 0 || $matricula === '') {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Prova e matrícula são obrigatórias.',
            ], 422);
        }

        if (! $request->hasFile('scan')) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Arquivo da prova não foi enviado.',
            ], 422);
        }

        $file = $request->file('scan');
        if (!is_array($file)) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Formato de arquivo inválido.',
            ], 422);
        }

        $pdo = LegacyConfig::createPdo();
        $provaOnline = $this->fetchProvaOnline($pdo, $examId);
        if ($provaOnline === null) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Prova não encontrada.',
            ], 404);
        }

        try {
            $prova = $this->resolveProvaAluno($pdo, $provaOnline, $matricula);
        } catch (RuntimeException $exception) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 422);
        }

        $attempt = $prova['next_attempt'];
        if ($attempt === null) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Limite de tentativas atingido para este aluno.',
            ], 409);
        }

        $storagePath = $this->storeUploadedFile($file, $examId, $matricula, $attempt);
        if ($storagePath === null) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível salvar o arquivo enviado.',
            ], 500);
        }

        $provider = ExamOmrConfig::driver();
        $metadata = [
            'original_name' => (string) ($file['name'] ?? ''),
            'mime_type' => (string) ($file['type'] ?? ''),
            'size' => isset($file['size']) ? (int) $file['size'] : null,
            'uploaded_at' => (new DateTimeImmutable())->format(DATE_ATOM),
        ];

        $scan = ExamScan::create([
            'provas_online_id' => $examId,
            'provas_id' => $prova['provas_id'],
            'student_matricula' => $matricula,
            'attempt' => $attempt,
            'status' => 'pending',
            'provider' => $provider,
            'file_path' => $storagePath,
            'metadata' => $metadata,
        ]);

        try {
            ProcessExamScan::dispatch((int) $scan['id']);
        } catch (Throwable $exception) {
            ExamScan::markFailed((int) $scan['id'], $exception->getMessage());

            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Falha ao iniciar o processamento do cartão.',
            ], 500);
        }

        return $this->json([
            'sucesso' => true,
            'scan_id' => (int) $scan['id'],
            'status' => $scan['status'],
            'attempt' => $attempt,
            'tentativas_restantes' => max(0, ExamOmrConfig::maxAttempts() - $attempt),
            'provas_id' => $prova['provas_id'],
        ]);
    }

    protected function status(Request $request)
    {
        $scanId = (int) $request->input('scan_id');
        if ($scanId <= 0) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Identificador do processamento é obrigatório.',
            ], 422);
        }

        $scan = ExamScan::find($scanId);
        if ($scan === null) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Processamento não encontrado.',
            ], 404);
        }

        $items = ExamScanItem::forScan($scanId);
        $metadata = $this->decodeMetadata($scan['metadata'] ?? null);

        return $this->json([
            'sucesso' => true,
            'dados' => [
                'id' => (int) $scan['id'],
                'status' => $scan['status'],
                'attempt' => (int) $scan['attempt'],
                'overall_confidence' => $scan['overall_confidence'] !== null ? (float) $scan['overall_confidence'] : null,
                'requires_review' => (int) ($scan['requires_review'] ?? 0) === 1,
                'score' => $scan['score'] !== null ? (float) $scan['score'] : null,
                'total_questions' => (int) ($scan['total_questions'] ?? 0),
                'correct_answers' => (int) ($scan['correct_answers'] ?? 0),
                'error_message' => $scan['error_message'] ?? null,
                'processed_at' => $scan['processed_at'] ?? null,
                'metadata' => $metadata,
                'itens' => array_map(function ($item) {
                    return [
                        'id' => (int) $item['id'],
                        'question_id' => (int) $item['question_id'],
                        'detected_alternative' => $item['detected_alternative'],
                        'confidence' => $item['confidence'] !== null ? (float) $item['confidence'] : null,
                        'status' => $item['status'],
                        'raw_payload' => $this->decodeMetadata($item['raw_payload'] ?? null),
                    ];
                }, $items),
            ],
        ]);
    }

    protected function attempts(Request $request)
    {
        $examId = (int) $request->input('exam_id');
        $matricula = trim((string) $request->input('matricula'));
        if ($examId <= 0 || $matricula === '') {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Prova e matrícula são obrigatórias.',
            ], 422);
        }

        $pdo = LegacyConfig::createPdo();
        $provaOnline = $this->fetchProvaOnline($pdo, $examId);
        if ($provaOnline === null) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Prova não encontrada.',
            ], 404);
        }

        $prova = $this->resolveProvaAluno($pdo, $provaOnline, $matricula, false);
        $tentativasFeitas = $prova['tentativa_feita'];
        $nextAttempt = $prova['next_attempt'];
        $scans = ExamScan::listByExamAndStudent($examId, $matricula);

        return $this->json([
            'sucesso' => true,
            'dados' => [
                'tentativas_feitas' => $tentativasFeitas,
                'proxima_tentativa' => $nextAttempt,
                'max_tentativas' => ExamOmrConfig::maxAttempts(),
                'provas_id' => $prova['provas_id'],
                'scans' => array_map(function ($scan) {
                    return [
                        'id' => (int) $scan['id'],
                        'status' => $scan['status'],
                        'attempt' => (int) $scan['attempt'],
                        'overall_confidence' => $scan['overall_confidence'] !== null ? (float) $scan['overall_confidence'] : null,
                        'requires_review' => (int) ($scan['requires_review'] ?? 0) === 1,
                        'created_at' => $scan['created_at'] ?? null,
                        'processed_at' => $scan['processed_at'] ?? null,
                        'metadata' => $this->decodeMetadata($scan['metadata'] ?? null),
                    ];
                }, $scans),
            ],
        ]);
    }

    /**
     * @param array<string,mixed>|null $metadata
     */
    private function decodeMetadata($metadata): mixed
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode($metadata, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function fetchProvaOnline(PDO $pdo, int $examId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM provas_online WHERE id = :id LIMIT 1');
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível preparar consulta de prova.');
        }

        $stmt->execute([':id' => $examId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string,mixed> $provaOnline
     * @return array{provas_id:int,tentativa_feita:int,next_attempt:?int}
     */
    private function resolveProvaAluno(PDO $pdo, array $provaOnline, string $matricula, bool $create = true): array
    {
        $stmt = $pdo->prepare('SELECT * FROM provas WHERE matricula = :matricula AND turma = :turma AND materia = :materia LIMIT 1');
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível preparar consulta da prova do aluno.');
        }

        $stmt->execute([
            ':matricula' => $matricula,
            ':turma' => $provaOnline['turma'] ?? '',
            ':materia' => $provaOnline['materia'] ?? '',
        ]);

        $provaAluno = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!is_array($provaAluno) && $create) {
            $insert = $pdo->prepare('INSERT INTO provas (matricula, turma, materia, tentativa_feita, data) VALUES (:matricula, :turma, :materia, 0, CURRENT_TIMESTAMP)');
            if ($insert === false) {
                throw new RuntimeException('Não foi possível cadastrar a prova do aluno.');
            }

            $insert->execute([
                ':matricula' => $matricula,
                ':turma' => $provaOnline['turma'] ?? '',
                ':materia' => $provaOnline['materia'] ?? '',
            ]);

            $stmt->execute([
                ':matricula' => $matricula,
                ':turma' => $provaOnline['turma'] ?? '',
                ':materia' => $provaOnline['materia'] ?? '',
            ]);
            $provaAluno = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        if (!is_array($provaAluno)) {
            throw new RuntimeException('Não foi possível identificar a prova do aluno.');
        }

        $tentativasFeitas = (int) ($provaAluno['tentativa_feita'] ?? 0);
        $max = ExamOmrConfig::maxAttempts();
        $nextAttempt = $tentativasFeitas >= $max ? null : min($max, $tentativasFeitas + 1);

        return [
            'provas_id' => (int) $provaAluno['id'],
            'tentativa_feita' => $tentativasFeitas,
            'next_attempt' => $nextAttempt,
        ];
    }

    /**
     * @param array<string,mixed> $file
     */
    private function storeUploadedFile(array $file, int $examId, string $matricula, int $attempt): ?string
    {
        $tmp = $file['tmp_name'] ?? '';
        if (!is_string($tmp) || $tmp === '') {
            return null;
        }

        $extension = pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION);
        if (!is_string($extension) || $extension === '') {
            $extension = 'jpg';
        }

        $safeMatricula = preg_replace('/[^A-Za-z0-9]/', '_', $matricula) ?? 'aluno';
        $filename = sprintf(
            '%s_exam%d_%s_attempt%d.%s',
            (new DateTimeImmutable())->format('YmdHis'),
            $examId,
            $safeMatricula,
            $attempt,
            strtolower($extension)
        );

        $directory = $this->storagePath('exams');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $destination = $directory . DIRECTORY_SEPARATOR . $filename;
        $moved = false;
        if (is_uploaded_file($tmp)) {
            $moved = move_uploaded_file($tmp, $destination);
        } elseif (is_readable($tmp)) {
            $moved = copy($tmp, $destination);
        }

        return $moved ? $destination : null;
    }

    private function storagePath(string $path = ''): string
    {
        $base = dirname(__DIR__, 2) . '/../storage/app';

        if ($path !== '') {
            return $base . '/' . ltrim($path, '/');
        }

        return $base;
    }
}
