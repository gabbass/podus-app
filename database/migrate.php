<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/autoload.php';

use PDO;

$pdo = LegacyConfig::createPdo();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$migrations = glob(__DIR__ . '/migrations/*.php');
sort($migrations);

foreach ($migrations as $migrationFile) {
    $migration = require $migrationFile;
    if (is_callable($migration)) {
        $migration($pdo);
        echo "Executed migration: " . basename($migrationFile) . PHP_EOL;
    }
}
