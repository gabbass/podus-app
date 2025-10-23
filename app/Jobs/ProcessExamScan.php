<?php

namespace App\Jobs;

use App\Events\GradeReleased;
use App\Models\ExamScan;
use App\Models\ExamScanItem;
use App\Services\ExamAutoGrader;
use App\Support\EventDispatcher;
use App\Support\LegacySchema;
use DateTimeImmutable;
use ExamOmrConfig;
use LegacyConfig;
use PDO;
use RuntimeException;
use Throwable;

class ProcessExamScan
{
    private int $scanId;
    private ExamAutoGrader $grader;
    private PDO $pdo;

    public function __construct(int $scanId, ?ExamAutoGrader $grader = null, ?PDO $pdo = null)
    {
        $this->scanId = $scanId;
        $this->grader = $grader ?? new ExamAutoGrader();
        $this->pdo = $pdo ?? LegacyConfig::createPdo();
    }

    public static function dispatch(int $scanId): void
    {
        $job = new self($scanId);
        $job->handle();
    }

    public function handle(): void
    {
        $scan = ExamScan::find($this->scanId);
        if ($scan === null) {
            return;
        }

        ExamScan::markProcessing($this->scanId);

        try {
            $result = $this->grader->grade($scan);
            $items = $result['items'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            ExamScanItem::replaceForScan($this->scanId, array_map(function ($item) {
                return [
                    'question_id' => $item['question_id'] ?? 0,
                    'detected_alternative' => $item['detected_alternative'] ?? null,
                    'confidence' => $item['confidence'] ?? null,
                    'status' => $item['status'] ?? 'detected',
                    'raw' => $item['raw'] ?? null,
                ];
            }, $items));

            $legacy = $result['legacy'] ?? [];
            $requiresReview = !empty($legacy['requires_review']) || !empty($result['requires_review']);

            $this->persistLegacyAnswers($scan, $legacy);

            $summary = $legacy['summary'] ?? [];
            $score = isset($summary['grade']) ? (float) $summary['grade'] : null;
            $totalQuestions = isset($summary['total_questions']) ? (int) $summary['total_questions'] : count($legacy['answers'] ?? []);
            $correctAnswers = isset($summary['correct']) ? (int) $summary['correct'] : 0;

            ExamScan::markCompleted($this->scanId, [
                'provider_scan_id' => $result['provider_scan_id'] ?? null,
                'overall_confidence' => $result['overall_confidence'] ?? null,
                'requires_review' => $requiresReview,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'metadata' => [
                    'raw_payload' => $result['raw'] ?? null,
                    'normalized' => $items,
                ],
                'processed_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);

            if (!$requiresReview) {
                $this->finalizeGrade($scan, $legacy);
            }
        } catch (Throwable $exception) {
            ExamScan::markFailed($this->scanId, $exception->getMessage(), method_exists($exception, 'getTrace') ? $exception->getTrace() : null);

            throw $exception;
        }
    }

    /**
     * @param array<string,mixed> $scan
     * @param array<string,mixed> $legacy
     */
    private function persistLegacyAnswers(array $scan, array $legacy): void
    {
        $answers = $legacy['answers'] ?? [];
        if (!is_array($answers) || $answers === []) {
            return;
        }

        $provaId = isset($scan['provas_id']) ? (int) $scan['provas_id'] : 0;
        if ($provaId <= 0) {
            return;
        }

        $attempt = (int) ($scan['attempt'] ?? 1);
        $attempt = max(1, min($attempt, ExamOmrConfig::maxAttempts()));
        $answerColumn = 'resposta_tenta' . $attempt;
        $gabaritoColumn = 'gabarito_tenta' . $attempt;

        $select = $this->pdo->prepare('SELECT id FROM respostas_alunos WHERE id_provas_online = :exam AND id_provas = :prova AND id_questao = :questao AND id_matricula = :matricula LIMIT 1');
        if ($select === false) {
            throw new RuntimeException('Não foi possível preparar consulta de respostas do aluno.');
        }

        $updateSql = sprintf(
            'UPDATE respostas_alunos SET %s = :resposta, %s = :gabarito WHERE id = :id',
            $answerColumn,
            $gabaritoColumn
        );
        $update = $this->pdo->prepare($updateSql);
        if ($update === false) {
            throw new RuntimeException('Não foi possível preparar atualização de respostas.');
        }

        $insertSql = sprintf(
            'INSERT INTO respostas_alunos (id_provas_online, id_provas, id_questao, id_matricula, %s, %s) VALUES (:exam, :prova, :questao, :matricula, :resposta, :gabarito)',
            $answerColumn,
            $gabaritoColumn
        );
        $insert = $this->pdo->prepare($insertSql);
        if ($insert === false) {
            throw new RuntimeException('Não foi possível preparar inserção de respostas.');
        }

        foreach ($answers as $answer) {
            if (!is_array($answer)) {
                continue;
            }

            $questionId = isset($answer['question_id']) ? (int) $answer['question_id'] : 0;
            if ($questionId === 0) {
                continue;
            }

            $detected = $answer['answer'] ?? null;
            $gabarito = $answer['gabarito'] ?? null;

            $select->execute([
                ':exam' => (int) $scan['provas_online_id'],
                ':prova' => $provaId,
                ':questao' => $questionId,
                ':matricula' => $scan['student_matricula'],
            ]);

            $existingId = $select->fetchColumn();
            if ($existingId !== false && $existingId !== null) {
                $update->execute([
                    ':resposta' => $detected,
                    ':gabarito' => $gabarito,
                    ':id' => (int) $existingId,
                ]);

                continue;
            }

            $insert->execute([
                ':exam' => (int) $scan['provas_online_id'],
                ':prova' => $provaId,
                ':questao' => $questionId,
                ':matricula' => $scan['student_matricula'],
                ':resposta' => $detected,
                ':gabarito' => $gabarito,
            ]);
        }
    }

    /**
     * @param array<string,mixed> $scan
     * @param array<string,mixed> $legacy
     */
    private function finalizeGrade(array $scan, array $legacy): void
    {
        $summary = $legacy['summary'] ?? [];
        if (!is_array($summary) || !isset($summary['grade'])) {
            return;
        }

        $provaId = isset($scan['provas_id']) ? (int) $scan['provas_id'] : 0;
        if ($provaId <= 0) {
            return;
        }

        $attempt = (int) ($scan['attempt'] ?? 1);
        $attempt = max(1, min($attempt, ExamOmrConfig::maxAttempts()));
        $gradeColumn = 'nota_tenta' . $attempt;
        $gradeValue = (float) $summary['grade'];

        $updateSql = sprintf(
            'UPDATE provas SET tentativa_feita = CASE WHEN tentativa_feita < :attempt THEN :attempt ELSE tentativa_feita END, %s = :nota WHERE id = :id',
            $gradeColumn
        );
        $update = $this->pdo->prepare($updateSql);
        if ($update === false) {
            throw new RuntimeException('Não foi possível atualizar a nota da prova.');
        }

        $update->execute([
            ':attempt' => $attempt,
            ':nota' => $gradeValue,
            ':id' => $provaId,
        ]);

        $this->dispatchGradeEvent($scan, $gradeValue);
    }

    private function dispatchGradeEvent(array $scan, float $grade): void
    {
        $examId = (int) $scan['provas_online_id'];
        $provaId = isset($scan['provas_id']) ? (int) $scan['provas_id'] : 0;
        if ($provaId <= 0) {
            return;
        }
        $matricula = (string) $scan['student_matricula'];

        $provaOnline = $this->fetchProvaOnline($examId);
        $student = $this->fetchAluno($matricula);

        $gradeItemId = null;
        if ($provaOnline && isset($provaOnline['moodle_grade_item_id'])) {
            $gradeItemId = $provaOnline['moodle_grade_item_id'] !== null ? (int) $provaOnline['moodle_grade_item_id'] : null;
        }

        $studentMoodleId = null;
        if ($student && isset($student['moodle_user_id'])) {
            $studentMoodleId = $student['moodle_user_id'] !== null ? (int) $student['moodle_user_id'] : null;
        }

        $studentId = $student ? (int) $student['id'] : 0;

        EventDispatcher::dispatch(new GradeReleased(
            gradeId: $provaId,
            examId: $examId,
            studentId: $studentId,
            grade: $grade,
            feedback: null,
            moodleGradeItemId: $gradeItemId,
            studentMoodleId: $studentMoodleId
        ));
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

    /**
     * @return array<string,mixed>|null
     */
    private function fetchAluno(string $matricula): ?array
    {
        $columns = ['id'];
        if (LegacySchema::hasColumn('login', 'moodle_user_id')) {
            $columns[] = 'moodle_user_id';
        }

        $sql = sprintf('SELECT %s FROM login WHERE matricula = :matricula LIMIT 1', implode(', ', $columns));
        $stmt = $this->pdo->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível consultar aluno.');
        }

        $stmt->execute([':matricula' => $matricula]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return is_array($row) ? $row : null;
    }
}
