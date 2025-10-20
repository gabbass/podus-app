<?php

namespace App\Auth\Policies;

use App\Auth\Profiles;

class PermissionMatrix
{
    public const RESERVATIONS_CREATE = 'reservas.create';
    public const RESERVATIONS_VIEW = 'reservas.view';
    public const RESERVATIONS_CANCEL = 'reservas.cancel';
    public const RESERVATIONS_APPROVE = 'reservas.approve';
    public const MATERIALS_CREATE = 'materiais.create';
    public const MATERIALS_EDIT = 'materiais.edit';
    public const MATERIALS_DELETE = 'materiais.delete';
    public const MATERIALS_VIEW = 'materiais.view';
    public const PLANNING_MANAGE = 'planejamento.manage';
    public const STUDENTS_VIEW = 'alunos.view';
    public const STUDENTS_GRADE = 'alunos.grade';

    /**
     * @var array<string, Profiles[]>
     */
    protected static array $matrix = [
        self::RESERVATIONS_CREATE => [Profiles::Administrator, Profiles::School, Profiles::Teacher],
        self::RESERVATIONS_VIEW => [Profiles::Administrator, Profiles::School, Profiles::Teacher, Profiles::Student],
        self::RESERVATIONS_CANCEL => [Profiles::Administrator, Profiles::School],
        self::RESERVATIONS_APPROVE => [Profiles::Administrator, Profiles::School],
        self::MATERIALS_CREATE => [Profiles::Administrator, Profiles::Teacher],
        self::MATERIALS_EDIT => [Profiles::Administrator, Profiles::Teacher],
        self::MATERIALS_DELETE => [Profiles::Administrator],
        self::MATERIALS_VIEW => [Profiles::Administrator, Profiles::Teacher, Profiles::Student],
        self::PLANNING_MANAGE => [Profiles::Administrator, Profiles::Teacher],
        self::STUDENTS_VIEW => [Profiles::Administrator, Profiles::Teacher, Profiles::School],
        self::STUDENTS_GRADE => [Profiles::Teacher],
    ];

    public static function allows(Profiles $profile, string $ability): bool
    {
        $allowed = self::$matrix[$ability] ?? [];
        foreach ($allowed as $allowedProfile) {
            if ($allowedProfile === $profile) {
                return true;
            }
        }

        return false;
    }

    public static function abilities(): array
    {
        return array_keys(self::$matrix);
    }
}
