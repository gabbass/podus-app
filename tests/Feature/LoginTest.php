<?php

namespace Tests\Feature;

use App\Auth\LegacySessionGuard;
use App\Auth\UserRepository;
use App\Support\Session\ArraySessionStore;
use App\Support\Session\SessionManager;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $pdo = $this->pdo();
        $pdo->exec('CREATE TABLE IF NOT EXISTS schools (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NULL,
            legacy_name TEXT NOT NULL UNIQUE,
            client_code TEXT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            legacy_id INTEGER NULL,
            login TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            email TEXT NULL,
            profile TEXT NOT NULL,
            school_id INTEGER NULL,
            matricula TEXT NULL,
            turma TEXT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS login (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            login TEXT,
            nome TEXT,
            perfil TEXT,
            escola TEXT,
            cliente TEXT,
            email TEXT,
            matricula TEXT,
            turma TEXT
        )');

        $pdo->exec("INSERT INTO login (login, nome, perfil, escola, cliente, email) VALUES
            ('prof', 'Paulo Professor', 'Professor', 'Escola Central', 'CLI001', 'prof@podus.test')");
    }

    public function testGuardLoadsUserFromSessionStore(): void
    {
        $sessionStore = new ArraySessionStore([
            'login' => 'prof',
            'perfil' => 'Professor',
            'escola' => 'Escola Central',
            'nome' => 'Paulo Professor',
        ]);
        SessionManager::swap($sessionStore);

        $repository = new UserRepository($this->pdo());
        $guard = LegacySessionGuard::fromGlobals($repository);
        $this->assertTrue($guard->check());

        $user = $guard->user();
        $this->assertNotNull($user);
        $this->assertSame('prof', $user->login);
        $this->assertSame('Paulo Professor', $user->name);

        $this->assertSame('Escola Central', session('escola'));

        $count = $this->pdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $this->assertSame(1, (int) $count);
    }
}
