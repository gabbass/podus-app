<?php

namespace App\Support;

use App\Http\JsonResponse;

class ResponseFactory
{
    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $headers
     */
    public function json(array $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}
