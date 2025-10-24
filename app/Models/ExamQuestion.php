<?php

namespace App\Models;

use LegacyConfig;
use PDO;

class ExamQuestion
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listAvailable(bool $isAdmin, ?int $legacyTeacherId = null, ?string $subject = null): array
    {
        $pdo = LegacyConfig::createPdo();

        $sql = 'SELECT id, questao, materia FROM questoes';
        $conditions = [];
        $params = [];

        if (! $isAdmin) {
            $restriction = '((isRestrito IS NULL OR isRestrito = 0)';
            if ($legacyTeacherId !== null) {
                $restriction .= ' OR id_professor = :prof';
                $params['prof'] = $legacyTeacherId;
            }
            $restriction .= ')';
            $conditions[] = $restriction;
        }

        if ($subject !== null && $subject !== '') {
            $conditions[] = 'materia = :materia';
            $params['materia'] = $subject;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
