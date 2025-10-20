<?php

namespace App\Http;

class Request
{
    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $body
     * @param array<string,mixed> $attributes
     */
    public function __construct(
        private array $query = [],
        private array $body = [],
        private array $attributes = [],
        private mixed $user = null
    ) {
    }

    public static function fromGlobals(): self
    {
        $input = $_POST;
        if ($_SERVER['REQUEST_METHOD'] ?? '' === 'POST' && empty($_POST)) {
            $raw = file_get_contents('php://input');
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }
        }

        return new self($_GET, $input, []);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '' && $value !== [];
    }

    public function setUser(mixed $user): void
    {
        $this->user = $user;
    }

    public function user(): mixed
    {
        return $this->user;
    }
}
