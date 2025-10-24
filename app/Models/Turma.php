<?php

namespace App\Models;

use LegacyConfig;
use PDO;

class Turma
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listForUser(bool $isAdmin, ?string $login = null): array
    {
        $pdo = LegacyConfig::createPdo();

        $sql = 'SELECT id, nome FROM turmas';
        $params = [];

        if (! $isAdmin) {
            $sql .= ' WHERE login = :login';
            $params['login'] = $login ?? '';
        }

        $sql .= ' ORDER BY nome';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
