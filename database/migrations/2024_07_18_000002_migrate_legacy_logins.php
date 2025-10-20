<?php

use App\Models\School;
use App\Auth\Profiles;
use PDO;

declare(strict_types=1);

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if (! tableExists($pdo, 'login')) {
        return;
    }

    $stmt = $pdo->query('SELECT * FROM login');
    if (! $stmt) {
        return;
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (! $rows) {
        return;
    }

    foreach ($rows as $row) {
        $login = $row['login'] ?? null;
        if (! $login) {
            continue;
        }

        $school = School::firstOrCreate($pdo, $row['escola'] ?? null, $row['cliente'] ?? null);
        $profile = Profiles::fromString((string) ($row['perfil'] ?? Profiles::Teacher->value)) ?? Profiles::Teacher;

        $existing = $pdo->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
        $existing->execute(['login' => $login]);
        $existingId = $existing->fetchColumn();

        if ($existingId) {
            $update = $pdo->prepare('UPDATE users SET name = :name, email = :email, profile = :profile, school_id = :school_id, matricula = :matricula, turma = :turma, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $update->execute([
                'name' => $row['nome'] ?? $login,
                'email' => $row['email'] ?? null,
                'profile' => $profile->value,
                'school_id' => $school?->id,
                'matricula' => $row['matricula'] ?? null,
                'turma' => $row['turma'] ?? null,
                'id' => $existingId,
            ]);
            continue;
        }

        $insert = $pdo->prepare('INSERT INTO users (legacy_id, login, name, email, profile, school_id, matricula, turma, created_at, updated_at) VALUES (:legacy_id, :login, :name, :email, :profile, :school_id, :matricula, :turma, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $insert->execute([
            'legacy_id' => $row['id'] ?? null,
            'login' => $login,
            'name' => $row['nome'] ?? $login,
            'email' => $row['email'] ?? null,
            'profile' => $profile->value,
            'school_id' => $school?->id,
            'matricula' => $row['matricula'] ?? null,
            'turma' => $row['turma'] ?? null,
        ]);
    }
};

function tableExists(PDO $pdo, string $table): bool
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $query = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :table");
        $query->execute(['table' => $table]);
        return (bool) $query->fetchColumn();
    }

    $query = $pdo->prepare("SHOW TABLES LIKE :table");
    $query->execute(['table' => $table]);
    return (bool) $query->fetchColumn();
}
