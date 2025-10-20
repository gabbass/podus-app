<?php

namespace App\Auth\Middleware;

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Profiles;
use App\Models\User;

abstract class EnsureProfileMiddleware
{
    protected LegacySessionGuard $guard;

    public function __construct(?LegacySessionGuard $guard = null)
    {
        $this->guard = $guard ?? LegacySessionGuard::fromGlobals();
    }

    abstract protected function profile(): Profiles;

    /**
     * @param mixed $request
     * @param callable $next
     * @return mixed
     */
    public function handle($request, callable $next)
    {
        $user = $this->guard->user();
        if (! $user instanceof User || ! $user->hasProfile($this->profile())) {
            throw new AuthorizationException('Você não tem permissão para acessar esta área.');
        }

        return $next($request, $user);
    }
}
