<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomReservation;
use DateTimeImmutable;
use InvalidArgumentException;
use LegacyConfig;
use PDO;
use PDOException;
use RuntimeException;

class RoomReservationService
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? LegacyConfig::createPdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return Room[]
     */
    public function getRoomsForSchool(?int $schoolId): array
    {
        if ($schoolId === null) {
            $stmt = $this->pdo->query('SELECT * FROM rooms ORDER BY name');
        } else {
            $stmt = $this->pdo->prepare('SELECT * FROM rooms WHERE school_id = :school OR school_id IS NULL ORDER BY name');
            $stmt->execute(['school' => $schoolId]);
        }

        $rows = $stmt?->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static fn (array $row): Room => Room::fromRow($row), $rows);
    }

    /**
     * @param array<string,mixed> $filters
     * @return RoomReservation[]
     */
    public function listReservations(array $filters = [], ?int $schoolId = null): array
    {
        $conditions = ['1 = 1'];
        $params = [];

        if (isset($filters['room_id'])) {
            $conditions[] = 'rr.room_id = :room_id';
            $params['room_id'] = (int) $filters['room_id'];
        }

        if (isset($filters['planning_id'])) {
            $conditions[] = 'rr.planning_id = :planning_id';
            $params['planning_id'] = (int) $filters['planning_id'];
        }

        if (isset($filters['starts_at'])) {
            $conditions[] = 'rr.ends_at > :starts_at';
            $params['starts_at'] = (string) $filters['starts_at'];
        }

        if (isset($filters['ends_at'])) {
            $conditions[] = 'rr.starts_at < :ends_at';
            $params['ends_at'] = (string) $filters['ends_at'];
        }

        if ($schoolId !== null) {
            $conditions[] = '(rooms.school_id = :school_id OR rooms.school_id IS NULL)';
            $params['school_id'] = $schoolId;
        }

        $sql = 'SELECT rr.*, rooms.name AS room_name, rooms.school_id AS room_school_id, '
            . 'reserved.name AS reserved_by_name, approver.name AS approved_by_name '
            . 'FROM room_reservations rr '
            . 'JOIN rooms ON rooms.id = rr.room_id '
            . 'JOIN users reserved ON reserved.id = rr.reserved_by '
            . 'LEFT JOIN users approver ON approver.id = rr.approved_by '
            . 'WHERE ' . implode(' AND ', $conditions)
            . ' ORDER BY rr.starts_at ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static fn (array $row): RoomReservation => RoomReservation::fromRow($row), $rows);
    }

    public function findReservation(int $reservationId, ?int $schoolId = null): ?RoomReservation
    {
        $filters = ['reservation_id' => $reservationId];
        $conditions = ['rr.id = :reservation_id'];
        $params = ['reservation_id' => $reservationId];

        if ($schoolId !== null) {
            $conditions[] = '(rooms.school_id = :school_id OR rooms.school_id IS NULL)';
            $params['school_id'] = $schoolId;
        }

        $sql = 'SELECT rr.*, rooms.name AS room_name, rooms.school_id AS room_school_id, '
            . 'reserved.name AS reserved_by_name, approver.name AS approved_by_name '
            . 'FROM room_reservations rr '
            . 'JOIN rooms ON rooms.id = rr.room_id '
            . 'JOIN users reserved ON reserved.id = rr.reserved_by '
            . 'LEFT JOIN users approver ON approver.id = rr.approved_by '
            . 'WHERE ' . implode(' AND ', $conditions)
            . ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? RoomReservation::fromRow($row) : null;
    }

    public function createReservation(
        int $roomId,
        int $planningId,
        int $reservedBy,
        string $startsAt,
        string $endsAt,
        bool $autoApprove = false,
        ?string $notes = null,
        ?int $reservedFor = null,
        ?int $schoolId = null,
        bool $allowOverride = false
    ): RoomReservation {
        $starts = $this->parseDateTime($startsAt);
        $ends = $this->parseDateTime($endsAt);

        if ($ends <= $starts) {
            throw new InvalidArgumentException('O horário final deve ser maior que o horário inicial.');
        }

        $room = $this->findRoom($roomId, $schoolId);
        if (! $room) {
            throw new RuntimeException('Sala não encontrada para o seu perfil.');
        }

        $this->ensurePlanningAccessible($planningId, $reservedBy, $allowOverride);

        $this->ensureNoConflict($roomId, $starts, $ends, null);

        $status = $autoApprove ? RoomReservation::STATUS_APPROVED : RoomReservation::STATUS_PENDING;
        $approvedBy = $autoApprove ? $reservedBy : null;
        $approvedAt = $autoApprove ? $starts : null;

        $stmt = $this->pdo->prepare('INSERT INTO room_reservations '
            . '(room_id, planning_id, reserved_by, reserved_for, status, starts_at, ends_at, notes, approved_by, approved_at, approval_comment, created_at, updated_at) '
            . 'VALUES (:room, :planning, :reserved_by, :reserved_for, :status, :starts, :ends, :notes, :approved_by, :approved_at, :comment, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');

        $stmt->execute([
            'room' => $roomId,
            'planning' => $planningId,
            'reserved_by' => $reservedBy,
            'reserved_for' => $reservedFor,
            'status' => $status,
            'starts' => $starts->format('Y-m-d H:i:s'),
            'ends' => $ends->format('Y-m-d H:i:s'),
            'notes' => $notes,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt ? $approvedAt->format('Y-m-d H:i:s') : null,
            'comment' => null,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        $reservation = $this->findReservation($id, $schoolId);
        if (! $reservation) {
            throw new RuntimeException('Falha ao carregar a reserva recém-criada.');
        }

        return $reservation;
    }

    public function updateStatus(int $reservationId, string $status, int $approverId, ?string $comment = null, ?int $schoolId = null): RoomReservation
    {
        $reservation = $this->findReservation($reservationId, $schoolId);
        if (! $reservation) {
            throw new RuntimeException('Reserva não encontrada.');
        }

        $allowedStatuses = [
            RoomReservation::STATUS_APPROVED,
            RoomReservation::STATUS_REJECTED,
            RoomReservation::STATUS_CANCELLED,
        ];

        if (! in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Status de aprovação inválido.');
        }

        if ($status === RoomReservation::STATUS_APPROVED) {
            $this->ensureNoConflict($reservation->roomId, $reservation->startsAt, $reservation->endsAt, $reservation->id);
        }

        $stmt = $this->pdo->prepare('UPDATE room_reservations SET status = :status, approved_by = :approver, approved_at = CURRENT_TIMESTAMP, approval_comment = :comment, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'status' => $status,
            'approver' => $approverId,
            'comment' => $comment,
            'id' => $reservationId,
        ]);

        $updated = $this->findReservation($reservationId, $schoolId);
        if (! $updated) {
            throw new RuntimeException('Não foi possível atualizar a reserva.');
        }

        return $updated;
    }

    public function cancelReservation(int $reservationId, int $userId, ?int $schoolId = null): RoomReservation
    {
        $reservation = $this->findReservation($reservationId, $schoolId);
        if (! $reservation) {
            throw new RuntimeException('Reserva não encontrada.');
        }

        if ($reservation->reservedBy !== $userId) {
            throw new RuntimeException('Você não possui permissão para cancelar esta reserva.');
        }

        $stmt = $this->pdo->prepare('UPDATE room_reservations SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'status' => RoomReservation::STATUS_CANCELLED,
            'id' => $reservationId,
        ]);

        $updated = $this->findReservation($reservationId, $schoolId);
        if (! $updated) {
            throw new RuntimeException('Não foi possível cancelar a reserva.');
        }

        return $updated;
    }

    protected function parseDateTime(string $value): DateTimeImmutable
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Data e hora são obrigatórias.');
        }

        // aceita formato HTML datetime-local (YYYY-MM-DDTHH:MM)
        $value = str_replace('T', ' ', $value);
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);

        if (! $date) {
            throw new InvalidArgumentException('Formato de data inválido.');
        }

        return $date;
    }

    protected function ensureNoConflict(int $roomId, DateTimeImmutable $start, DateTimeImmutable $end, ?int $ignoreId = null): void
    {
        $sql = 'SELECT COUNT(*) FROM room_reservations '
            . 'WHERE room_id = :room '
            . 'AND status IN (:status_pending, :status_approved) '
            . 'AND ends_at > :start '
            . 'AND starts_at < :end';

        if ($ignoreId !== null) {
            $sql .= ' AND id != :ignore_id';
        }

        $stmt = $this->pdo->prepare($sql);

        $params = [
            'room' => $roomId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
            'status_pending' => RoomReservation::STATUS_PENDING,
            'status_approved' => RoomReservation::STATUS_APPROVED,
        ];

        if ($ignoreId !== null) {
            $params['ignore_id'] = $ignoreId;
        }

        $stmt->execute($params);
        $count = (int) $stmt->fetchColumn();

        if ($count > 0) {
            throw new RuntimeException('Já existe uma reserva para este período.');
        }
    }

    protected function findRoom(int $roomId, ?int $schoolId): ?Room
    {
        $conditions = ['id = :id'];
        $params = ['id' => $roomId];

        if ($schoolId !== null) {
            $conditions[] = '(school_id = :school OR school_id IS NULL)';
            $params['school'] = $schoolId;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM rooms WHERE ' . implode(' AND ', $conditions) . ' LIMIT 1');
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Room::fromRow($row) : null;
    }

    /**
     * @return array<string,mixed>
     */
    protected function ensurePlanningAccessible(int $planningId, int $userId, bool $allowOverride): array
    {
        if (! $this->tableExists('planejamento')) {
            return ['id' => $planningId];
        }

        $stmt = $this->pdo->prepare('SELECT id, login FROM planejamento WHERE id = :id');
        $stmt->execute(['id' => $planningId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            throw new RuntimeException('Planejamento informado não foi encontrado.');
        }

        $ownerId = isset($row['login']) ? (int) $row['login'] : null;
        if (! $allowOverride && $ownerId !== null && $ownerId !== $userId) {
            throw new RuntimeException('Você não pode reservar para um planejamento de outro professor.');
        }

        return $row;
    }

    protected function tableExists(string $table): bool
    {
        try {
            $stmt = $this->pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '" . $table . "' LIMIT 1");
            if ($stmt && $stmt->fetch()) {
                return true;
            }
        } catch (PDOException) {
            // ignora, pode ser SQLite
        }

        $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:table LIMIT 1");
        $stmt->execute(['table' => $table]);

        return (bool) $stmt->fetch();
    }
}
