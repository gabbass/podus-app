<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class MoodleClient
{
    private string $endpoint;
    private string $token;
    private int $timeout;

    public function __construct(string $endpoint, string $token, int $timeout = 10)
    {
        $endpoint = trim($endpoint);
        if ($endpoint === '') {
            throw new RuntimeException('Moodle endpoint is not configured.');
        }

        $token = trim($token);
        if ($token === '') {
            throw new RuntimeException('Moodle token is not configured.');
        }

        $this->endpoint = rtrim($endpoint, '/');
        $this->token = $token;
        $this->timeout = $timeout > 0 ? $timeout : 10;
    }

    public static function fromEnv(): self
    {
        $endpoint = self::env('MOODLE_ENDPOINT', '');
        $token = self::env('MOODLE_TOKEN', '');
        $timeout = (int) self::env('MOODLE_TIMEOUT', 10);

        return new self($endpoint, $token, $timeout);
    }

    public function call(string $function, array $params = []): array
    {
        $url = $this->buildUrl($function);
        $payload = $this->buildPayload($params);

        $responseBody = $this->sendRequest($url, $payload);

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            $message = 'Invalid JSON returned by Moodle.';
            $this->logError($message, $responseBody, $payload);
            throw new RuntimeException($message);
        }

        if (isset($decoded['exception']) || isset($decoded['errorcode'])) {
            $message = $decoded['message'] ?? $decoded['errorcode'] ?? 'Unknown Moodle error.';
            $this->logError($message, $decoded, $payload);
            throw new RuntimeException($message);
        }

        return $decoded;
    }

    private function buildUrl(string $function): string
    {
        if ($function === '') {
            throw new RuntimeException('The wsfunction parameter is required.');
        }

        return sprintf(
            '%s/webservice/rest/server.php?wstoken=%s&moodlewsrestformat=json&wsfunction=%s',
            $this->endpoint,
            urlencode($this->token),
            urlencode($function)
        );
    }

    private function buildPayload(array $params): string
    {
        return http_build_query($params, '', '&');
    }

    private function sendRequest(string $url, string $payload): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            $message = 'Unable to initialize cURL.';
            $this->logError($message, null, $payload);
            throw new RuntimeException($message);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $message = $error !== '' ? $error : 'Unknown cURL error while calling Moodle.';
            $this->logError($message, null, $payload);
            throw new RuntimeException($message);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = sprintf('Moodle request failed with HTTP %d.', $httpCode);
            $this->logError($message, $response, $payload);
            throw new RuntimeException($message);
        }

        return $response;
    }

    private function logError(string $message, $response, $payload): void
    {
        $context = [
            'message' => $message,
            'response' => $response,
            'payload' => $payload,
        ];

        try {
            error_log('[MoodleClient] ' . json_encode($context, JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            error_log('[MoodleClient] ' . $message);
        }
    }

    private static function env(string $key, $default = null)
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }
}

