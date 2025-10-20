<?php

namespace App\Http;

class JsonResponse
{
    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $headers
     */
    public function __construct(
        private array $data,
        private int $status = 200,
        private array $headers = []
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string,string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value, true);
        }
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
