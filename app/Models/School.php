<?php

namespace App\Models;

use PDO;
use RuntimeException;

class School
{
    public ?int $id;
    public string $name;
    public ?string $slug;
    public ?string $legacyName;
    public ?string $clientCode;

    public function __construct(?int $id, string $name, ?string $slug = null, ?string $legacyName = null, ?string $clientCode = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->legacyName = $legacyName ?? $name;
        $this->clientCode = $clientCode;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['name'] ?? $row['nome'] ?? $row['escola'] ?? ''),
            $row['slug'] ?? null,
            $row['legacy_name'] ?? ($row['escola'] ?? null),
            $row['client_code'] ?? ($row['cliente'] ?? null)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'legacy_name' => $this->legacyName,
            'client_code' => $this->clientCode,
        ];
    }

    public static function firstOrCreate(PDO $pdo, ?string $legacyName, ?string $clientCode = null): ?self
    {
        $legacyName = $legacyName ? trim($legacyName) : null;
        if ($legacyName === null || $legacyName === '') {
            return null;
        }

        $select = $pdo->prepare('SELECT * FROM schools WHERE legacy_name = :legacy LIMIT 1');
        $select->execute(['legacy' => $legacyName]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return self::fromRow($row);
        }

        $insert = $pdo->prepare('INSERT INTO schools (name, legacy_name, client_code, slug, created_at, updated_at) VALUES (:name, :legacy, :client, :slug, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $slug = self::slugify($legacyName);
        if (! $insert->execute([
            'name' => $legacyName,
            'legacy' => $legacyName,
            'client' => $clientCode,
            'slug' => $slug,
        ])) {
            throw new RuntimeException('Unable to create school record.');
        }

        $id = (int) $pdo->lastInsertId();
        return new self($id, $legacyName, $slug, $legacyName, $clientCode);
    }

    protected static function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = preg_replace('/-+/', '-', (string) $slug);
        return trim($slug ?? '', '-');
    }
}
