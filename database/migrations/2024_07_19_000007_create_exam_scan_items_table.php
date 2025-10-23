<?php

use PDO;

declare(strict_types=1);

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $pdo->exec('CREATE TABLE IF NOT EXISTS exam_scan_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_scan_id BIGINT UNSIGNED NOT NULL,
            question_id BIGINT UNSIGNED NOT NULL,
            detected_alternative VARCHAR(10) NULL,
            confidence DECIMAL(5,4) NULL,
            status VARCHAR(30) NOT NULL DEFAULT "detected",
            raw_payload JSON NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_exam_scan_items_scan (exam_scan_id),
            CONSTRAINT fk_exam_scan_items_scan FOREIGN KEY (exam_scan_id) REFERENCES exam_scans(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        return;
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS exam_scan_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exam_scan_id INTEGER NOT NULL,
        question_id INTEGER NOT NULL,
        detected_alternative VARCHAR(10) NULL,
        confidence REAL NULL,
        status VARCHAR(30) NOT NULL DEFAULT "detected",
        raw_payload TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (exam_scan_id) REFERENCES exam_scans(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_exam_scan_items_scan ON exam_scan_items (exam_scan_id)');
};
