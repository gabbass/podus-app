<?php

use PDO;

declare(strict_types=1);

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $pdo->exec('CREATE TABLE IF NOT EXISTS rooms (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            school_id BIGINT UNSIGNED NULL,
            name VARCHAR(255) NOT NULL,
            capacity INT NULL,
            location VARCHAR(255) NULL,
            description TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_rooms_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL,
            CONSTRAINT uq_rooms_school_name UNIQUE (school_id, name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        return;
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        school_id INTEGER NULL,
        name VARCHAR(255) NOT NULL,
        capacity INTEGER NULL,
        location VARCHAR(255) NULL,
        description TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (school_id, name),
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
    )');
};
