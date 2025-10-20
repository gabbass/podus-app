<?php

namespace App\Models;

use DateTimeImmutable;

class Room
{
    public ?int $id;
    public ?int $schoolId;
    public string $name;
    public ?int $capacity;
    public ?string $location;
    public ?string $description;
    public ?DateTimeImmutable $createdAt;
    public ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        ?int $schoolId,
        string $name,
        ?int $capacity = null,
        ?string $location = null,
        ?string $description = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->schoolId = $schoolId;
        $this->name = $name;
        $this->capacity = $capacity;
        $this->location = $location;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param array<string,mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            isset($row['school_id']) ? (int) $row['school_id'] : null,
            (string) ($row['name'] ?? ''),
            isset($row['capacity']) ? (int) $row['capacity'] : null,
            $row['location'] ?? null,
            $row['description'] ?? null,
            isset($row['created_at']) ? new DateTimeImmutable((string) $row['created_at']) : null,
            isset($row['updated_at']) ? new DateTimeImmutable((string) $row['updated_at']) : null
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->schoolId,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'location' => $this->location,
            'description' => $this->description,
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }
}
