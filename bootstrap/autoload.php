<?php

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $relative = str_replace('App\\', '', $class);
        $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require_once $path;
        }
    }
});

if (! class_exists('LegacyConfig')) {
    require_once __DIR__ . '/../config/legacy.php';
}

if (! class_exists('ExamOmrConfig')) {
    require_once __DIR__ . '/../config/exam_omr.php';
}

require_once __DIR__ . '/../app/Support/helpers.php';

require_once __DIR__ . '/events.php';
