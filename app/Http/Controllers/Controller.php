<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;

abstract class Controller
{
    protected function json(array $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}
