<?php

namespace App\Support\Session;

interface SessionStore
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, mixed $value): void;
}
