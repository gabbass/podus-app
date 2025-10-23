<?php

namespace App\Auth;

use App\Models\User;
use App\Support\Session\SessionManager;
use App\Support\Session\SessionStore;

class LegacySessionGuard
{
    protected SessionStore $session;
    protected UserRepository $repository;
    protected ?User $cachedUser = null;

    public function __construct(SessionStore $session, ?UserRepository $repository = null)
    {
        $this->session = $session;
        $this->repository = $repository ?? new UserRepository();
    }

    public static function fromGlobals(?UserRepository $repository = null): self
    {
        return new self(SessionManager::instance(), $repository);
    }

    public function user(): ?User
    {
        if ($this->cachedUser instanceof User) {
            return $this->cachedUser;
        }

        $this->cachedUser = $this->repository->syncFromSession($this->session->all());
        return $this->cachedUser;
    }

    public function check(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * @return array<string, mixed>
     */
    public function session(): array
    {
        return $this->session->all();
    }
}
