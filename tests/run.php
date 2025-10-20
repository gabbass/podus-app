<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/autoload.php';

$testFiles = array_filter(glob(__DIR__ . '/**/*.php'), static function ($file) {
    return basename($file) !== 'run.php';
});

$failures = 0;
foreach ($testFiles as $file) {
    try {
        require $file;
    } catch (Throwable $throwable) {
        $failures++;
        fwrite(STDERR, sprintf("Test failure in %s: %s\n", $file, $throwable->getMessage()));
    }
}

if ($failures > 0) {
    fwrite(STDERR, sprintf("%d test(s) failed.\n", $failures));
    exit(1);
}

echo "All tests passed (" . count($testFiles) . ")\n";
