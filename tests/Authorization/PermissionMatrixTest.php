<?php

declare(strict_types=1);

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\AdminMiddleware;
use App\Auth\Middleware\AdminOrProfessorMiddleware;
use App\Auth\Middleware\AlunoMiddleware;
use App\Auth\Middleware\ProfessorMiddleware;
use App\Auth\Policies\PermissionMatrix;
use App\Auth\Profiles;
use App\Auth\UserRepository;

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE schools (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NULL,
    legacy_name TEXT NOT NULL UNIQUE,
    client_code TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE users (
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

$pdo->exec('CREATE TABLE login (
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
    ('admin', 'Ana Admin', 'Administrador', 'Escola Central', 'CLI001', 'admin@podus.test'),
    ('prof', 'Paulo Professor', 'Professor', 'Escola Central', 'CLI001', 'prof@podus.test'),
    ('aluno', 'Alice Aluna', 'Aluno', 'Escola Central', 'CLI001', 'aluno@podus.test')");

$repository = new UserRepository($pdo);

$teacherSession = [
    'login' => 'prof',
    'perfil' => 'Professor',
    'escola' => 'Escola Central',
    'nome' => 'Paulo Professor',
];

$teacherGuard = new LegacySessionGuard($teacherSession, $repository);
$teacher = $teacherGuard->user();

if (! $teacher) {
    throw new RuntimeException('Professor não foi carregado.');
}

if (! $teacher->hasProfile(Profiles::Teacher)) {
    throw new RuntimeException('Perfil do professor não reconhecido.');
}

if (! $teacher->can(PermissionMatrix::MATERIALS_EDIT)) {
    throw new RuntimeException('Professor deveria poder editar materiais.');
}

if ($teacher->can(PermissionMatrix::RESERVATIONS_CANCEL)) {
    throw new RuntimeException('Professor não deveria cancelar reservas.');
}

$profMiddleware = new ProfessorMiddleware($teacherGuard);
$profMiddleware->handle([], static function ($request, $user) use ($teacher) {
    if ($user !== $teacher) {
        throw new RuntimeException('Middleware não repassou o usuário.');
    }

    return true;
});

$studentSession = [
    'login' => 'aluno',
    'perfil' => 'Aluno',
    'escola' => 'Escola Central',
    'nome' => 'Alice Aluna',
    'matricula' => '12345',
    'turma' => '1A',
];

$studentGuard = new LegacySessionGuard($studentSession, $repository);
$student = $studentGuard->user();

if (! $student || ! $student->hasProfile(Profiles::Student)) {
    throw new RuntimeException('Aluno não foi carregado.');
}

if ($student->can(PermissionMatrix::MATERIALS_EDIT)) {
    throw new RuntimeException('Aluno não deveria editar materiais.');
}

$studentMiddleware = new AlunoMiddleware($studentGuard);
$studentMiddleware->handle([], static function () {
    return true;
});

$failed = false;
try {
    $studentMiddlewareProfessor = new ProfessorMiddleware($studentGuard);
    $studentMiddlewareProfessor->handle([], static function () {
        return true;
    });
} catch (AuthorizationException $exception) {
    $failed = true;
}

if (! $failed) {
    throw new RuntimeException('Aluno não deveria passar pelo middleware de professor.');
}

$adminGuard = new LegacySessionGuard([
    'login' => 'admin',
    'perfil' => 'Administrador',
    'escola' => 'Escola Central',
    'nome' => 'Ana Admin',
], $repository);

$admin = $adminGuard->user();
if (! $admin || ! $admin->hasProfile(Profiles::Administrator)) {
    throw new RuntimeException('Administrador não carregado.');
}

$adminMiddleware = new AdminMiddleware($adminGuard);
$adminMiddleware->handle([], static function () {
    return true;
});

$sharedMiddleware = new AdminOrProfessorMiddleware($teacherGuard);
$sharedMiddleware->handle([], static function () {
    return true;
});

if (! PermissionMatrix::allows(Profiles::Teacher, PermissionMatrix::STUDENTS_GRADE)) {
    throw new RuntimeException('Professor deveria poder lançar notas.');
}

if (! PermissionMatrix::allows(Profiles::Administrator, PermissionMatrix::RESERVATIONS_APPROVE)) {
    throw new RuntimeException('Administrador deveria aprovar reservas.');
}

if (PermissionMatrix::allows(Profiles::Teacher, PermissionMatrix::RESERVATIONS_APPROVE)) {
    throw new RuntimeException('Professor não deveria aprovar reservas.');
}

if (PermissionMatrix::allows(Profiles::Student, PermissionMatrix::STUDENTS_GRADE)) {
    throw new RuntimeException('Aluno não pode lançar notas.');
}
