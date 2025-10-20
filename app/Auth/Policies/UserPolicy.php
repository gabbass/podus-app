<?php

namespace App\Auth\Policies;

use App\Auth\Profiles;
use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasProfile(Profiles::Administrator)) {
            return true;
        }

        return null;
    }

    public function can(User $user, string $ability): bool
    {
        $pre = $this->before($user, $ability);
        if ($pre !== null) {
            return $pre;
        }

        return $user->can($ability);
    }
}
