<?php

namespace App\Models;

use LegacyConfig;
use PDO;

class Materia
{
    /**
     * @return array<int,array{id:int,label:string}>
     */
    public static function listAll(): array
    {
        $pdo = LegacyConfig::createPdo();
        $stmt = $pdo->query('SELECT id, nome FROM materias ORDER BY nome');
        if ($stmt === false) {
            return [];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) {
            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'label' => (string) $row['nome'],
            ];
        }, $rows);
    }
}
