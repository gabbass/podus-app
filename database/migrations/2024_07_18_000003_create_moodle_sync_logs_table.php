<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $pdo->exec('CREATE TABLE IF NOT EXISTS moodle_sync_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(100) NOT NULL,
            entity_id VARCHAR(191) NULL,
            action VARCHAR(100) NOT NULL,
            status VARCHAR(50) NOT NULL,
            payload JSON NOT NULL,
            response JSON NULL,
            error_message TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        return;
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS moodle_sync_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type VARCHAR(100) NOT NULL,
        entity_id VARCHAR(191) NULL,
        action VARCHAR(100) NOT NULL,
        status VARCHAR(50) NOT NULL,
        payload TEXT NOT NULL,
        response TEXT NULL,
        error_message TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
};

