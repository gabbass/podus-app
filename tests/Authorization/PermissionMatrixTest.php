<?php

namespace Tests\Authorization;

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\AdminMiddleware;
use App\Auth\Middleware\AdminOrProfessorMiddleware;
use App\Auth\Middleware\AlunoMiddleware;
use App\Auth\Middleware\ProfessorMiddleware;
use App\Auth\Policies\PermissionMatrix;
use App\Auth\Profiles;
use App\Auth\UserRepository;
use App\Support\Session\ArraySessionStore;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
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

        $pdo->exec("INSERT INTO login (login, nome, perfil, escola, cliente, email, matricula, turma) VALUES
            ('admin', 'Ana Admin', 'Administrador', 'Escola Central', 'CLI001', 'admin@podus.test', NULL, NULL),
            ('prof', 'Paulo Professor', 'Professor', 'Escola Central', 'CLI001', 'prof@podus.test', NULL, NULL),
            ('aluno', 'Alice Aluna', 'Aluno', 'Escola Central', 'CLI001', 'aluno@podus.test', '12345', '1A')");
    }

    public function testPoliciesAndGuardsRespectProfiles(): void
    {
        $repository = new UserRepository($this->pdo());

        $teacherSession = new ArraySessionStore([
            'login' => 'prof',
            'perfil' => 'Professor',
            'escola' => 'Escola Central',
            'nome' => 'Paulo Professor',
        ]);

        $teacherGuard = new LegacySessionGuard($teacherSession, $repository);
        $teacher = $teacherGuard->user();
        $this->assertNotNull($teacher);
        $this->assertTrue($teacher->hasProfile(Profiles::Teacher));
        $this->assertTrue($teacher->can(PermissionMatrix::MATERIALS_EDIT));
        $this->assertFalse($teacher->can(PermissionMatrix::RESERVATIONS_CANCEL));

        $profMiddleware = new ProfessorMiddleware($teacherGuard);
        $result = $profMiddleware->handle([], static fn ($request, $user) => $user);
        $this->assertSame($teacher, $result);

        $studentStore = new ArraySessionStore([
            'login' => 'aluno',
            'perfil' => 'Aluno',
            'escola' => 'Escola Central',
            'nome' => 'Alice Aluna',
            'matricula' => '12345',
            'turma' => '1A',
        ]);

        $studentGuard = new LegacySessionGuard($studentStore, $repository);
        $student = $studentGuard->user();
        $this->assertNotNull($student);
        $this->assertTrue($student->hasProfile(Profiles::Student));
        $this->assertFalse($student->can(PermissionMatrix::MATERIALS_EDIT));

        $studentMiddleware = new AlunoMiddleware($studentGuard);
        $this->assertTrue($studentMiddleware->handle([], static fn () => true));

        $this->expectException(AuthorizationException::class);
        (new ProfessorMiddleware($studentGuard))->handle([], static fn () => true);
    }

    public function testAdminAndSharedMiddleware(): void
    {
        $repository = new UserRepository($this->pdo());

        $adminGuard = new LegacySessionGuard(new ArraySessionStore([
            'login' => 'admin',
            'perfil' => 'Administrador',
            'escola' => 'Escola Central',
            'nome' => 'Ana Admin',
        ]), $repository);

        $admin = $adminGuard->user();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasProfile(Profiles::Administrator));

        $adminMiddleware = new AdminMiddleware($adminGuard);
        $this->assertTrue($adminMiddleware->handle([], static fn () => true));

        $teacherSession = new ArraySessionStore([
            'login' => 'prof',
            'perfil' => 'Professor',
            'escola' => 'Escola Central',
            'nome' => 'Paulo Professor',
        ]);
        $teacherGuard = new LegacySessionGuard($teacherSession, $repository);

        $sharedMiddleware = new AdminOrProfessorMiddleware($teacherGuard);
        $this->assertTrue($sharedMiddleware->handle([], static fn () => true));

        $this->assertTrue(PermissionMatrix::allows(Profiles::Teacher, PermissionMatrix::STUDENTS_GRADE));
        $this->assertTrue(PermissionMatrix::allows(Profiles::Administrator, PermissionMatrix::RESERVATIONS_APPROVE));
        $this->assertFalse(PermissionMatrix::allows(Profiles::Teacher, PermissionMatrix::RESERVATIONS_APPROVE));
        $this->assertFalse(PermissionMatrix::allows(Profiles::Student, PermissionMatrix::STUDENTS_GRADE));
    }
}
