<?php

namespace App\Models;

use App\Auth\Profiles;
use App\Auth\Policies\PermissionMatrix;

class User
{
    public ?int $id;
    public ?int $legacyId;
    public string $login;
    public string $name;
    public ?string $email;
    public Profiles $profile;
    public ?School $school;
    public ?string $matricula;
    public ?string $turma;

    public function __construct(
        ?int $id,
        string $login,
        string $name,
        Profiles $profile,
        ?string $email = null,
        ?School $school = null,
        ?int $legacyId = null,
        ?string $matricula = null,
        ?string $turma = null
    ) {
        $this->id = $id;
        $this->login = $login;
        $this->name = $name;
        $this->profile = $profile;
        $this->email = $email;
        $this->school = $school;
        $this->legacyId = $legacyId;
        $this->matricula = $matricula;
        $this->turma = $turma;
    }

    public static function fromRow(array $row, ?School $school = null): self
    {
        $profile = $row['profile'] ?? $row['perfil'] ?? Profiles::Teacher->value;
        $profileEnum = $profile instanceof Profiles ? $profile : (Profiles::fromString((string) $profile) ?? Profiles::Teacher);

        $school ??= isset($row['school_name']) || isset($row['school_id'])
            ? new School(
                isset($row['school_id']) ? (int) $row['school_id'] : null,
                (string) ($row['school_name'] ?? $row['escola'] ?? ''),
                $row['school_slug'] ?? null,
                $row['school_legacy'] ?? ($row['escola'] ?? null),
                $row['school_client_code'] ?? ($row['cliente'] ?? null)
            )
            : null;

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['login'] ?? ''),
            (string) ($row['name'] ?? $row['nome'] ?? ''),
            $profileEnum,
            $row['email'] ?? null,
            $school,
            isset($row['legacy_id']) ? (int) $row['legacy_id'] : (isset($row['id']) ? (int) $row['id'] : null),
            $row['matricula'] ?? null,
            $row['turma'] ?? null
        );
    }

    public function hasProfile(Profiles|string $profile): bool
    {
        if (! $profile instanceof Profiles) {
            $profile = Profiles::fromString((string) $profile) ?? Profiles::Teacher;
        }

        return $this->profile === $profile;
    }

    public function can(string $ability): bool
    {
        return PermissionMatrix::allows($this->profile, $ability);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'legacy_id' => $this->legacyId,
            'login' => $this->login,
            'name' => $this->name,
            'email' => $this->email,
            'profile' => $this->profile->value,
            'school' => $this->school?->toArray(),
            'matricula' => $this->matricula,
            'turma' => $this->turma,
        ];
    }
}
