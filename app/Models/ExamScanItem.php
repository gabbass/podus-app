<?php

namespace App\Models;

use DateTimeImmutable;
use LegacyConfig;
use PDO;
use RuntimeException;

class ExamScanItem
{
    /**
     * @param array<int,array<string,mixed>> $items
     */
    public static function replaceForScan(int $scanId, array $items): void
    {
        $pdo = LegacyConfig::createPdo();
        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare('DELETE FROM exam_scan_items WHERE exam_scan_id = :scan_id');
            if ($delete === false) {
                throw new RuntimeException('Unable to prepare exam_scan_items delete.');
            }
            $delete->execute([':scan_id' => $scanId]);

            if ($items === []) {
                $pdo->commit();

                return;
            }

            $stmt = $pdo->prepare('INSERT INTO exam_scan_items (
                exam_scan_id, question_id, detected_alternative, confidence, status, raw_payload, created_at, updated_at
            ) VALUES (:exam_scan_id, :question_id, :detected_alternative, :confidence, :status, :raw_payload, :created_at, :updated_at)');

            if ($stmt === false) {
                throw new RuntimeException('Unable to prepare exam_scan_items insert.');
            }

            $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            foreach ($items as $item) {
                $raw = $item['raw'] ?? null;
                if (is_array($raw)) {
                    $raw = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                $stmt->execute([
                    ':exam_scan_id' => $scanId,
                    ':question_id' => $item['question_id'] ?? 0,
                    ':detected_alternative' => $item['detected_alternative'] ?? null,
                    ':confidence' => $item['confidence'] ?? null,
                    ':status' => $item['status'] ?? 'detected',
                    ':raw_payload' => $raw,
                    ':created_at' => $now,
                    ':updated_at' => $now,
                ]);
            }

            $pdo->commit();
        } catch (RuntimeException $exception) {
            $pdo->rollBack();

            throw $exception;
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function forScan(int $scanId): array
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->prepare('SELECT * FROM exam_scan_items WHERE exam_scan_id = :scan ORDER BY id');
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare exam_scan_items select.');
        }

        $stmt->execute([':scan' => $scanId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
