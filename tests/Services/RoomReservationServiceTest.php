<?php

declare(strict_types=1);

use App\Models\RoomReservation;
use App\Services\RoomReservationService;

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE schools (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NULL,
    legacy_name TEXT NOT NULL,
    client_code TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    legacy_id INTEGER NULL,
    login TEXT NOT NULL,
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

$pdo->exec('CREATE TABLE rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id INTEGER NULL,
    name TEXT NOT NULL,
    capacity INTEGER NULL,
    location TEXT NULL,
    description TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (school_id, name)
)');

$pdo->exec('CREATE TABLE planejamento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login INTEGER NULL,
    nome TEXT NULL
)');

$pdo->exec('CREATE TABLE room_reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL,
    planning_id INTEGER NOT NULL,
    reserved_by INTEGER NOT NULL,
    reserved_for INTEGER NULL,
    status TEXT NOT NULL,
    starts_at TEXT NOT NULL,
    ends_at TEXT NOT NULL,
    notes TEXT NULL,
    approved_by INTEGER NULL,
    approved_at TEXT NULL,
    approval_comment TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec("INSERT INTO schools (name, legacy_name) VALUES ('Escola Central', 'Escola Central')");
$pdo->exec("INSERT INTO users (login, name, profile, school_id) VALUES ('prof', 'Paulo Professor', 'Professor', 1)");
$pdo->exec("INSERT INTO users (login, name, profile, school_id) VALUES ('admin', 'Ana Admin', 'Administrador', 1)");
$pdo->exec("INSERT INTO rooms (school_id, name, capacity) VALUES (1, 'Laboratório 1', 30)");
$pdo->exec("INSERT INTO planejamento (login, nome) VALUES (1, 'Plano Teste')");

$service = new RoomReservationService($pdo);

$rooms = $service->getRoomsForSchool(1);
if (count($rooms) !== 1 || $rooms[0]->name !== 'Laboratório 1') {
    throw new RuntimeException('Sala não carregada corretamente.');
}

$reservation = $service->createReservation(
    roomId: 1,
    planningId: 1,
    reservedBy: 1,
    startsAt: '2024-08-01 09:00',
    endsAt: '2024-08-01 10:00',
    autoApprove: false,
    notes: 'Uso de laboratório de ciências',
    schoolId: 1,
    allowOverride: false
);

if ($reservation->status !== RoomReservation::STATUS_PENDING) {
    throw new RuntimeException('Reservas de professores devem iniciar como pendentes.');
}

$failed = false;
try {
    $service->createReservation(
        roomId: 1,
        planningId: 1,
        reservedBy: 1,
        startsAt: '2024-08-01 09:30',
        endsAt: '2024-08-01 10:30',
        autoApprove: false,
        notes: 'Conflito',
        schoolId: 1,
        allowOverride: false
    );
} catch (RuntimeException $exception) {
    $failed = true;
}

if (! $failed) {
    throw new RuntimeException('Reservas conflitantes não foram bloqueadas.');
}

$approved = $service->updateStatus($reservation->id ?? 0, RoomReservation::STATUS_APPROVED, 2, 'Aprovado', 1);
if ($approved->status !== RoomReservation::STATUS_APPROVED) {
    throw new RuntimeException('Reserva não foi aprovada.');
}

$cancelled = $service->cancelReservation($reservation->id ?? 0, 1, 1);
if ($cancelled->status !== RoomReservation::STATUS_CANCELLED) {
    throw new RuntimeException('Cancelamento pelo autor deveria funcionar.');
}

$adminReservation = $service->createReservation(
    roomId: 1,
    planningId: 1,
    reservedBy: 2,
    startsAt: '2024-08-02 09:00',
    endsAt: '2024-08-02 10:00',
    autoApprove: true,
    notes: 'Reserva imediata',
    schoolId: 1,
    allowOverride: true
);

if ($adminReservation->status !== RoomReservation::STATUS_APPROVED) {
    throw new RuntimeException('Reservas aprovadas automaticamente deveriam ficar aprovadas.');
}

$list = $service->listReservations(['planning_id' => 1], 1);
if (count($list) < 2) {
    throw new RuntimeException('Listagem de reservas deveria retornar itens.');
}
