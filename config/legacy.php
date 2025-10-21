<?php

if (!class_exists('LegacyConfig')) {
    class LegacyConfig
    {
        private const DEFAULT_MENUS = [
            'Professor' => [
                ['Início', 'dashboard-professor.php', 'fa-home'],
                ['Minhas turmas', 'pesquisar-turmas.php', 'fa-people-line'],
                ['Meus alunos', 'pesquisar-alunos.php', 'fa-users'],
                ['Material pedagógico', 'material-pedagogico.php', 'fa-book'],
                ['Planejamento de aulas', 'planejador-mensal.php', 'fas fa-calendar-week'],
                ['Banco de questões', 'questoes.php', 'fa-question-circle'],
                ['Provas', 'provas.php', 'fa-file-alt'],
                ['Notas', 'notas-ap.php', 'fa-calculator'],
                ['Jogos pedagógicos', 'jogos-pedagocicos.php', 'fas fa-gamepad'],
            ],
            'Administrador' => [
                ['Início', 'dashboard.php', 'fa-home'],
                ['Usuários', 'usuarios.php', 'fa-user-cog'],
                ['Turmas', 'cadastrar-turmas.php', 'fa-people-line'],
                ['Planejamento de aulas', 'planejador-mensal.php', 'fas fa-calendar-week'],
                ['Banco de questões', 'questoes.php', 'fa-question-circle'],
                ['Provas', 'provas.php', 'fa-file-alt'],
                ['Notas', 'notas-ap.php', 'fa-calculator'],
                ['Relatórios', 'relatorios.php', 'fa-chart-bar'],
            ],
            'Aluno' => [
                ['Início', 'dashboard-aluno.php', 'fa-home'],
                ['Material de apoio', 'material-apoio.php', 'fa-book'],
                ['Minhas Notas', 'nota-provas.php', 'fa-calculator'],
            ],
        ];

        private const DEFAULT_MAIL_CONFIGS = [
            'default' => [
                'host' => 'smtp.hostinger.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username' => 'contato@heliosander.com.br',
                'password' => 'L=Pq;8|U1x',
                'from_address' => 'contato@heliosander.com.br',
                'from_name' => 'Portal Universo do Saber',
                'debug' => 0,
            ],
            'invite' => [
                'host' => 'smtp.hostinger.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username' => 'contato@heliosander.com.br',
                'password' => 'L=Pq;8|U1x',
                'from_address' => 'contato@heliosander.com.br',
                'from_name' => 'Portal Universo do Saber',
                'debug' => 2,
            ],
            'recovery' => [
                'host' => 'smtp.titan.email',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'contato@portaluniversodosaber.com.br',
                'password' => 'jPpb0#4w@15',
                'from_address' => 'contato@portaluniversodosaber.com.br',
                'from_name' => 'Portal Universo do Saber - Esqueci a senha',
                'debug' => 0,
            ],
            'contact' => [
                'host' => 'smtp.titan.email',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'contato@portaluniversodosaber.com.br',
                'password' => 'jPpb0#4w@15',
                'from_address' => 'contato@portaluniversodosaber.com.br',
                'from_name' => 'Portal Universo do Saber - Contato',
                'to_address' => 'contato@portaluniversodosaber.com.br',
                'cc_address' => 'contato@portaluniversodosaber.com.br',
                'debug' => 0,
            ],
        ];

        private const DEFAULT_RECAPTCHA = [
            'secret_key' => '6Ld4zIIrAAAAAOFJSzeom29DzogYjGeV31Fn_Z4o',
            'site_keys' => [
                'CHECKBOX' => '6Ld4zIIrAAAAAOaFzWz-lYjiYI_sS2Xk8r3-MEjJ',
                'V3' => '6Le4nHUrAAAAAHBIOPt9ttWSExzv0mwxhCHbHnQI',
            ],
        ];

        private const MAIL_KEY_SUFFIXES = [
            'host' => 'HOST',
            'port' => 'PORT',
            'encryption' => 'ENCRYPTION',
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
            'from_address' => 'FROM_ADDRESS',
            'from_name' => 'FROM_NAME',
            'to_address' => 'TO_ADDRESS',
            'cc_address' => 'CC_ADDRESS',
            'bcc_address' => 'BCC_ADDRESS',
            'debug' => 'DEBUG_LEVEL',
        ];

        private static bool $envLoaded = false;

        private static function bootEnv(): void
        {
            if (self::$envLoaded) {
                return;
            }

            $envFile = dirname(__DIR__) . '/.env';
            if (!isset($_ENV) || !is_array($_ENV)) {
                $_ENV = [];
            }

            if (is_readable($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lines !== false) {
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

        public static function dbDsn(): string
        {
            $connection = strtolower((string) self::env('DB_CONNECTION', 'mysql'));
            if ($connection === 'sqlite') {
                $database = (string) self::env('DB_DATABASE', ':memory:');
                if ($database === '' || $database === ':memory:') {
                    return 'sqlite::memory:';
                }

                if (! str_contains($database, DIRECTORY_SEPARATOR)) {
                    $database = dirname(__DIR__) . '/' . ltrim($database, '/');
                }

                return 'sqlite:' . $database;
            }

            $host = (string) self::env('DB_HOST', 'localhost');
            $port = self::env('DB_PORT');
            $dbname = self::env('DB_NAME', '');
            $charset = (string) self::env('DB_CHARSET', 'utf8');

            $dsn = "mysql:host={$host}";
            if (!empty($port)) {
                $dsn .= ';port=' . $port;
            }
            if ($dbname !== '') {
                $dsn .= ';dbname=' . $dbname;
            }
            if ($charset !== '') {
                $dsn .= ';charset=' . $charset;
            }

            return $dsn;
        }

        public static function dbUser(): string
        {
            if (strtolower((string) self::env('DB_CONNECTION', 'mysql')) === 'sqlite') {
                return '';
            }

            return (string) self::env('DB_USER', 'root');
        }

        public static function dbPassword(): string
        {
            if (strtolower((string) self::env('DB_CONNECTION', 'mysql')) === 'sqlite') {
                return '';
            }

            return (string) self::env('DB_PASS', '');
        }

        public static function createPdo(): PDO
        {
            return new PDO(
                self::dbDsn(),
                self::dbUser(),
                self::dbPassword(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        public static function appTitle(): string
        {
            return (string) self::env('APP_TITLE', 'iDivas - Agenda on-line para Mulheres');
        }

        public static function sidebarTitle(): string
        {
            return (string) self::env('APP_SIDEBAR_TITLE', 'Universo Saber');
        }

        public static function uploadMaxSizeBytes(): int
        {
            $value = self::env('UPLOAD_MAX_SIZE_MB', 10);
            if (is_string($value) && $value !== '') {
                $value = (float) $value;
            }
            if (!is_numeric($value)) {
                $value = 10;
            }

            return (int) round(((float) $value) * 1024 * 1024);
        }

        public static function menus(): array
        {
            $raw = self::env('UI_MENUS_JSON');
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            return self::DEFAULT_MENUS;
        }

        public static function menuForProfile(string $profile): array
        {
            $menus = self::menus();
            return $menus[$profile] ?? [];
        }

        public static function mailConfig(string $channel = 'default'): array
        {
            $normalized = strtolower($channel);
            $defaults = self::DEFAULT_MAIL_CONFIGS[$normalized] ?? self::DEFAULT_MAIL_CONFIGS['default'];
            $prefix = 'MAIL_' . strtoupper($normalized) . '_';
            $config = $defaults;

            foreach (self::MAIL_KEY_SUFFIXES as $key => $suffix) {
                $envKey = $prefix . $suffix;
                $value = self::env($envKey);
                if ($value === null || $value === '') {
                    continue;
                }

                if ($key === 'port' || $key === 'debug') {
                    $config[$key] = (int) $value;
                    continue;
                }

                $config[$key] = $value;
            }

            return $config;
        }

        public static function recaptchaSecretKey(): string
        {
            return (string) self::env('RECAPTCHA_SECRET_KEY', self::DEFAULT_RECAPTCHA['secret_key']);
        }

        public static function recaptchaSiteKey(string $variant = 'checkbox'): ?string
        {
            $normalized = strtoupper($variant);
            $envKey = 'RECAPTCHA_SITE_KEY_' . $normalized;
            $value = self::env($envKey);
            if (is_string($value) && $value !== '') {
                return $value;
            }

            return self::DEFAULT_RECAPTCHA['site_keys'][$normalized] ?? null;
        }
    }
}
