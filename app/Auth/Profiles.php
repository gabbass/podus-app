<?php

namespace App\Auth;

enum Profiles: string
{
    case Administrator = 'Administrador';
    case School = 'Escola';
    case Teacher = 'Professor';
    case Student = 'Aluno';

    public static function fromString(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }

        return null;
    }

    public function label(): string
    {
        return $this->value;
    }
}
