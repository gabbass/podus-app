<?php

namespace App\Auth\Middleware;

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Profiles;
use App\Models\User;

class AdminOrProfessorMiddleware
{
    protected LegacySessionGuard $guard;

    public function __construct(?LegacySessionGuard $guard = null)
    {
        $this->guard = $guard ?? LegacySessionGuard::fromGlobals();
    }

    public function handle($request, callable $next)
    {
        $user = $this->guard->user();
        if (! $user instanceof User) {
            throw new AuthorizationException('Você precisa estar autenticado.');
        }

        if (! $user->hasProfile(Profiles::Administrator) && ! $user->hasProfile(Profiles::Teacher)) {
            throw new AuthorizationException('Você não tem permissão para acessar esta área.');
        }

        return $next($request, $user);
    }
}
