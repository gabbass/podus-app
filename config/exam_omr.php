<?php

if (!class_exists('ExamOmrConfig')) {
    class ExamOmrConfig
    {
        private const DEFAULTS = [
            'driver' => 'saas',
            'timeout' => 30,
            'min_confidence' => 0.5,
            'review_threshold' => 0.85,
            'max_attempts' => 3,
        ];

        private static bool $envLoaded = false;

        private static function bootEnv(): void
        {
            if (self::$envLoaded) {
                return;
            }

            if (!function_exists('putenv')) {
                self::$envLoaded = true;

                return;
            }

            $envFile = dirname(__DIR__) . '/.env';
            if (!is_readable($envFile)) {
                self::$envLoaded = true;

                return;
            }

            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                self::$envLoaded = true;

                return;
            }

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                    continue;
                }

                $delimiterPos = strpos($line, '=');
                if ($delimiterPos === false) {
                    continue;
                }

                $name = trim(substr($line, 0, $delimiterPos));
                $value = trim(substr($line, $delimiterPos + 1));
                $value = self::stripQuotes($value);

                if ($name !== '' && getenv($name) === false) {
                    $_ENV[$name] = $value;
                    putenv($name . '=' . $value);
                }
            }

            self::$envLoaded = true;
        }

        private static function stripQuotes(string $value): string
        {
            if ($value === '') {
                return $value;
            }

            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                return substr($value, 1, -1);
            }

            return $value;
        }

        private static function env(string $key, $default = null)
        {
            self::bootEnv();

            if (array_key_exists($key, $_ENV)) {
                return $_ENV[$key];
            }

            $value = getenv($key);
            if ($value !== false) {
                return $value;
            }

            return $default;
        }

        public static function driver(): string
        {
            $driver = (string) self::env('EXAM_OMR_DRIVER', self::DEFAULTS['driver']);

            return $driver !== '' ? strtolower($driver) : self::DEFAULTS['driver'];
        }

        /**
         * @return array<string,mixed>
         */
        public static function providerConfig(?string $driver = null): array
        {
            $driver = $driver ? strtolower($driver) : self::driver();
            $timeout = (int) self::env('EXAM_OMR_TIMEOUT', self::DEFAULTS['timeout']);

            if ($driver === 'self_hosted' || $driver === 'microservice') {
                return [
                    'driver' => 'self_hosted',
                    'base_url' => (string) self::env('EXAM_OMR_SELF_HOSTED_BASE_URL', 'http://omr:8000'),
                    'token' => (string) self::env('EXAM_OMR_SELF_HOSTED_TOKEN', ''),
                    'timeout' => $timeout > 0 ? $timeout : self::DEFAULTS['timeout'],
                ];
            }

            return [
                'driver' => 'saas',
                'base_url' => (string) self::env('EXAM_OMR_SAAS_BASE_URL', ''),
                'api_key' => (string) self::env('EXAM_OMR_SAAS_API_KEY', ''),
                'timeout' => $timeout > 0 ? $timeout : self::DEFAULTS['timeout'],
            ];
        }

        public static function minConfidence(): float
        {
            $value = self::env('EXAM_OMR_MIN_CONFIDENCE', self::DEFAULTS['min_confidence']);

            return self::toFloat($value, self::DEFAULTS['min_confidence']);
        }

        public static function reviewThreshold(): float
        {
            $value = self::env('EXAM_OMR_REVIEW_THRESHOLD', self::DEFAULTS['review_threshold']);

            return self::toFloat($value, self::DEFAULTS['review_threshold']);
        }

        public static function maxAttempts(): int
        {
            $value = (int) self::env('EXAM_OMR_MAX_ATTEMPTS', self::DEFAULTS['max_attempts']);

            return $value > 0 ? $value : self::DEFAULTS['max_attempts'];
        }

        private static function toFloat($value, float $default): float
        {
            if (is_numeric($value)) {
                return (float) $value;
            }

            if (is_string($value) && $value !== '' && is_numeric($value)) {
                return (float) $value;
            }

            return $default;
        }
    }
}
