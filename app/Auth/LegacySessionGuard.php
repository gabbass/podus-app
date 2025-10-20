<?php

namespace App\Auth;

use App\Models\User;

class LegacySessionGuard
{
    protected array $session;
    protected UserRepository $repository;
    protected ?User $cachedUser = null;

    public function __construct(array $session, ?UserRepository $repository = null)
    {
        $this->session = $session;
        $this->repository = $repository ?? new UserRepository();
    }

    public static function fromGlobals(?UserRepository $repository = null): self
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return new self($_SESSION ?? [], $repository);
    }

    public function user(): ?User
    {
        if ($this->cachedUser instanceof User) {
            return $this->cachedUser;
        }

        $this->cachedUser = $this->repository->syncFromSession($this->session);
        return $this->cachedUser;
    }

    public function check(): bool
    {
        return $this->user() instanceof User;
    }

    public function session(): array
    {
        return $this->session;
    }
}
