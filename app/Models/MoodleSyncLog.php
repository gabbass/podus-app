<?php

namespace App\Models;

use DateTimeImmutable;
use LegacyConfig;
use RuntimeException;
use Throwable;

class MoodleSyncLog
{
    public static function record(
        string $entityType,
        ?string $entityId,
        string $action,
        string $status,
        array $payload,
        ?array $response = null,
        ?string $errorMessage = null
    ): void {
        $pdo = LegacyConfig::createPdo();

        $stmt = $pdo->prepare('INSERT INTO moodle_sync_logs (
            entity_type, entity_id, action, status, payload, response, error_message, created_at
        ) VALUES (:entity_type, :entity_id, :action, :status, :payload, :response, :error_message, :created_at)');

        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare statement for moodle_sync_logs.');
        }

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $payloadJson = self::encodeJson($payload);
        $responseJson = $response === null ? null : self::encodeJson($response);

        $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':action' => $action,
            ':status' => $status,
            ':payload' => $payloadJson,
            ':response' => $responseJson,
            ':error_message' => $errorMessage,
            ':created_at' => $now,
        ]);
    }

    private static function encodeJson(array $data): string
    {
        try {
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            return json_encode(
                [
                    'encoding_error' => $exception->getMessage(),
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) ?: '{}';
        }
    }
}

