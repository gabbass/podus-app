<?php

namespace App\Models;

use DateTimeImmutable;

class RoomReservation
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public ?int $id;
    public int $roomId;
    public int $planningId;
    public int $reservedBy;
    public ?int $reservedFor;
    public string $status;
    public DateTimeImmutable $startsAt;
    public DateTimeImmutable $endsAt;
    public ?string $notes;
    public ?int $approvedBy;
    public ?DateTimeImmutable $approvedAt;
    public ?string $approvalComment;
    public ?DateTimeImmutable $createdAt;
    public ?DateTimeImmutable $updatedAt;

    public ?string $roomName = null;
    public ?string $reservedByName = null;
    public ?string $approvedByName = null;

    public function __construct(
        ?int $id,
        int $roomId,
        int $planningId,
        int $reservedBy,
        ?int $reservedFor,
        string $status,
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        ?string $notes = null,
        ?int $approvedBy = null,
        ?DateTimeImmutable $approvedAt = null,
        ?string $approvalComment = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->roomId = $roomId;
        $this->planningId = $planningId;
        $this->reservedBy = $reservedBy;
        $this->reservedFor = $reservedFor;
        $this->status = $status;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->notes = $notes;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = $approvedAt;
        $this->approvalComment = $approvalComment;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param array<string,mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $reservation = new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['room_id'],
            (int) $row['planning_id'],
            (int) $row['reserved_by'],
            isset($row['reserved_for']) ? (int) $row['reserved_for'] : null,
            (string) $row['status'],
            new DateTimeImmutable((string) $row['starts_at']),
            new DateTimeImmutable((string) $row['ends_at']),
            $row['notes'] ?? null,
            isset($row['approved_by']) ? (int) $row['approved_by'] : null,
            isset($row['approved_at']) && $row['approved_at'] !== null ? new DateTimeImmutable((string) $row['approved_at']) : null,
            $row['approval_comment'] ?? null,
            isset($row['created_at']) ? new DateTimeImmutable((string) $row['created_at']) : null,
            isset($row['updated_at']) ? new DateTimeImmutable((string) $row['updated_at']) : null
        );

        $reservation->roomName = $row['room_name'] ?? null;
        $reservation->reservedByName = $row['reserved_by_name'] ?? null;
        $reservation->approvedByName = $row['approved_by_name'] ?? null;

        return $reservation;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->roomId,
            'planning_id' => $this->planningId,
            'reserved_by' => $this->reservedBy,
            'reserved_for' => $this->reservedFor,
            'status' => $this->status,
            'starts_at' => $this->startsAt->format('c'),
            'ends_at' => $this->endsAt->format('c'),
            'notes' => $this->notes,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt?->format('c'),
            'approval_comment' => $this->approvalComment,
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
            'room_name' => $this->roomName,
            'reserved_by_name' => $this->reservedByName,
            'approved_by_name' => $this->approvedByName,
        ];
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
