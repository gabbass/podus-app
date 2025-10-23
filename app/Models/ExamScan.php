<?php

namespace App\Models;

use DateTimeImmutable;
use LegacyConfig;
use PDO;
use RuntimeException;

class ExamScan
{
    /**
     * @param array<string,mixed> $attributes
     * @return array<string,mixed>
     */
    public static function create(array $attributes): array
    {
        $pdo = LegacyConfig::createPdo();

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO exam_scans (
            provas_online_id, provas_id, student_matricula, attempt, status, provider, file_path, metadata, created_at, updated_at
        ) VALUES (:provas_online_id, :provas_id, :student_matricula, :attempt, :status, :provider, :file_path, :metadata, :created_at, :updated_at)');

        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scans insert statement.');
        }

        $metadata = $attributes['metadata'] ?? null;
        if (is_array($metadata)) {
            $metadata = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $stmt->execute([
            ':provas_online_id' => (int) ($attributes['provas_online_id'] ?? 0),
            ':provas_id' => $attributes['provas_id'] ?? null,
            ':student_matricula' => (string) ($attributes['student_matricula'] ?? ''),
            ':attempt' => (int) ($attributes['attempt'] ?? 1),
            ':status' => (string) ($attributes['status'] ?? 'pending'),
            ':provider' => (string) ($attributes['provider'] ?? 'saas'),
            ':file_path' => (string) ($attributes['file_path'] ?? ''),
            ':metadata' => $metadata,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $id = (int) $pdo->lastInsertId();

        return self::find($id) ?? [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->prepare('SELECT * FROM exam_scans WHERE id = :id LIMIT 1');
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scans select.');
        }

        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return is_array($record) ? $record : null;
    }

    public static function markProcessing(int $id): void
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->prepare('UPDATE exam_scans SET status = :status, updated_at = :updated_at WHERE id = :id');
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scans update.');
        }

        $stmt->execute([
            ':status' => 'processing',
            ':updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function markCompleted(int $id, array $payload): void
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->prepare('UPDATE exam_scans SET
            status = :status,
            provider_scan_id = :provider_scan_id,
            overall_confidence = :overall_confidence,
            requires_review = :requires_review,
            score = :score,
            total_questions = :total_questions,
            correct_answers = :correct_answers,
            metadata = :metadata,
            processed_at = :processed_at,
            updated_at = :updated_at
        WHERE id = :id');

        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scans completion update.');
        }

        $metadata = $payload['metadata'] ?? null;
        if (is_array($metadata)) {
            $metadata = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $stmt->execute([
            ':status' => 'completed',
            ':provider_scan_id' => $payload['provider_scan_id'] ?? null,
            ':overall_confidence' => $payload['overall_confidence'] ?? null,
            ':requires_review' => empty($payload['requires_review']) ? 0 : 1,
            ':score' => $payload['score'] ?? null,
            ':total_questions' => $payload['total_questions'] ?? 0,
            ':correct_answers' => $payload['correct_answers'] ?? 0,
            ':metadata' => $metadata,
            ':processed_at' => $payload['processed_at'] ?? (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ':updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    public static function markFailed(int $id, string $message, ?array $raw = null): void
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->prepare('UPDATE exam_scans SET status = :status, error_message = :error_message, metadata = :metadata, updated_at = :updated_at WHERE id = :id');
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scans failure update.');
        }

        $metadata = null;
        if (is_array($raw)) {
            $metadata = json_encode(['raw_payload' => $raw], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $stmt->execute([
            ':status' => 'failed',
            ':error_message' => $message,
            ':metadata' => $metadata,
            ':updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listByExamAndStudent(int $examId, string $matricula): array
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->prepare('SELECT * FROM exam_scans WHERE provas_online_id = :exam AND student_matricula = :matricula ORDER BY id DESC');
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scans list query.');
        }

        $stmt->execute([
            ':exam' => $examId,
            ':matricula' => $matricula,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
