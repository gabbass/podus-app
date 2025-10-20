<?php

namespace App\Auth;

use App\Auth\Profiles;
use App\Models\School;
use App\Models\User;
use LegacyConfig;
use PDO;
use RuntimeException;

class UserRepository
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? LegacyConfig::createPdo();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function findByLogin(string $login): ?User
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, s.id as school_id, s.name as school_name, s.slug as school_slug, s.legacy_name as school_legacy, s.client_code as school_client_code '
            . 'FROM users u '
            . 'LEFT JOIN schools s ON s.id = u.school_id '
            . 'WHERE u.login = :login LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? User::fromRow($row) : null;
    }

    public function syncFromSession(array $session): ?User
    {
        $login = $session['login'] ?? null;
        if (! $login) {
            return null;
        }

        $user = $this->findByLogin($login);
        if (! $user) {
            $legacy = $this->findLegacyLogin($login);
            if ($legacy === null) {
                $legacy = [
                    'login' => $login,
                    'nome' => $session['nome'] ?? $login,
                    'perfil' => $session['perfil'] ?? Profiles::Teacher->value,
                    'escola' => $session['escola'] ?? null,
                    'cliente' => $session['cliente'] ?? null,
                    'email' => $session['email'] ?? null,
                    'matricula' => $session['matricula'] ?? null,
                    'turma' => $session['turma'] ?? null,
                ];
            }

            $user = $this->persistFromLegacy($legacy);
        }

        $profile = Profiles::fromString((string) ($session['perfil'] ?? $user->profile->value));
        if ($profile && ! $user->hasProfile($profile)) {
            $this->updateProfile($user, $profile);
            $user->profile = $profile;
        }

        return $user;
    }

    protected function persistFromLegacy(array $legacyRow): User
    {
        $profile = Profiles::fromString((string) ($legacyRow['perfil'] ?? Profiles::Teacher->value)) ?? Profiles::Teacher;
        $school = School::firstOrCreate($this->pdo, $legacyRow['escola'] ?? null, $legacyRow['cliente'] ?? null);

        $stmt = $this->pdo->prepare('INSERT INTO users (legacy_id, login, name, email, profile, school_id, matricula, turma, created_at, updated_at) '
            . 'VALUES (:legacy_id, :login, :name, :email, :profile, :school_id, :matricula, :turma, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');

        $legacyId = isset($legacyRow['id']) ? (int) $legacyRow['id'] : null;
        $params = [
            'legacy_id' => $legacyId,
            'login' => $legacyRow['login'] ?? '',
            'name' => $legacyRow['nome'] ?? ($legacyRow['login'] ?? ''),
            'email' => $legacyRow['email'] ?? null,
            'profile' => $profile->value,
            'school_id' => $school?->id,
            'matricula' => $legacyRow['matricula'] ?? null,
            'turma' => $legacyRow['turma'] ?? null,
        ];

        if (! $stmt->execute($params)) {
            throw new RuntimeException('Unable to persist legacy user.');
        }

        $id = (int) $this->pdo->lastInsertId();
        return new User($id, $params['login'], $params['name'], $profile, $params['email'], $school, $legacyId, $params['matricula'], $params['turma']);
    }

    protected function updateProfile(User $user, Profiles $profile): void
    {
        if ($user->id === null) {
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE users SET profile = :profile, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['profile' => $profile->value, 'id' => $user->id]);
    }

    protected function findLegacyLogin(string $login): ?array
    {
        if (! $this->tableExists('login')) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM login WHERE login = :login LIMIT 1');
        $stmt->execute(['login' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    protected function tableExists(string $table): bool
    {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
        if ($stmt !== false && $stmt->fetch()) {
            return true;
        }

        $stmt = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt !== false && $stmt->fetch()) {
            return true;
        }

        return false;
    }
}
