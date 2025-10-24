<?php

namespace App\Models;

use LegacyConfig;
use PDO;
use RuntimeException;

class Exam
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function list(bool $isAdmin, ?string $login = null): array
    {
        $pdo = LegacyConfig::createPdo();

        $sql = 'SELECT id, turma, materia, escola, lista_quest FROM provas_online';
        $params = [];

        if (! $isAdmin) {
            $sql .= ' WHERE login = :login';
            $params['login'] = $login ?? '';
        }

        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function find(int $id, bool $isAdmin, ?string $login = null): ?array
    {
        $pdo = LegacyConfig::createPdo();

        $sql = 'SELECT * FROM provas_online WHERE id = :id';
        $params = ['id' => $id];

        if (! $isAdmin) {
            $sql .= ' AND login = :login LIMIT 1';
            $params['login'] = $login ?? '';
        } else {
            $sql .= ' LIMIT 1';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function create(array $payload, string $login): int
    {
        $pdo = LegacyConfig::createPdo();

        $stmt = $pdo->prepare('INSERT INTO provas_online (data, turma, materia, login, escola, lista_quest) VALUES (CURRENT_DATE, :turma, :materia, :login, :escola, :lista)');

        $params = [
            'turma' => $payload['turma'] ?? '',
            'materia' => $payload['materia'] ?? '',
            'login' => $login,
            'escola' => $payload['escola'] ?? null,
            'lista' => $payload['lista_quest'] ?? '',
        ];

        if (! $stmt->execute($params)) {
            throw new RuntimeException('Falha ao criar a prova.');
        }

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function update(int $id, array $payload, bool $isAdmin, ?string $login = null): bool
    {
        $pdo = LegacyConfig::createPdo();

        $sql = 'UPDATE provas_online SET turma = :turma, materia = :materia, escola = :escola, lista_quest = :lista WHERE id = :id';
        $params = [
            'turma' => $payload['turma'] ?? '',
            'materia' => $payload['materia'] ?? '',
            'escola' => $payload['escola'] ?? null,
            'lista' => $payload['lista_quest'] ?? '',
            'id' => $id,
        ];

        if (! $isAdmin) {
            $sql .= ' AND login = :login';
            $params['login'] = $login ?? '';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, bool $isAdmin, ?string $login = null): bool
    {
        $pdo = LegacyConfig::createPdo();

        $sql = 'DELETE FROM provas_online WHERE id = :id';
        $params = ['id' => $id];

        if (! $isAdmin) {
            $sql .= ' AND login = :login';
            $params['login'] = $login ?? '';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }
}
