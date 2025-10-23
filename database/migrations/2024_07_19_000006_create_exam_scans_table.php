<?php

use PDO;

declare(strict_types=1);

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $pdo->exec('CREATE TABLE IF NOT EXISTS exam_scans (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            provas_online_id BIGINT UNSIGNED NOT NULL,
            provas_id BIGINT UNSIGNED NULL,
            student_matricula VARCHAR(50) NOT NULL,
            attempt TINYINT UNSIGNED NOT NULL DEFAULT 1,
            status VARCHAR(30) NOT NULL DEFAULT "pending",
            provider VARCHAR(50) NOT NULL,
            provider_scan_id VARCHAR(100) NULL,
            file_path VARCHAR(255) NOT NULL,
            overall_confidence DECIMAL(5,4) NULL,
            requires_review TINYINT(1) NOT NULL DEFAULT 0,
            error_message TEXT NULL,
            metadata JSON NULL,
            score DECIMAL(5,2) NULL,
            total_questions INT UNSIGNED NOT NULL DEFAULT 0,
            correct_answers INT UNSIGNED NOT NULL DEFAULT 0,
            processed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_exam_scans_exam_student (provas_online_id, student_matricula),
            INDEX idx_exam_scans_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        return;
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS exam_scans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        provas_online_id INTEGER NOT NULL,
        provas_id INTEGER NULL,
        student_matricula VARCHAR(50) NOT NULL,
        attempt INTEGER NOT NULL DEFAULT 1,
        status VARCHAR(30) NOT NULL DEFAULT "pending",
        provider VARCHAR(50) NOT NULL,
        provider_scan_id VARCHAR(100) NULL,
        file_path VARCHAR(255) NOT NULL,
        overall_confidence REAL NULL,
        requires_review INTEGER NOT NULL DEFAULT 0,
        error_message TEXT NULL,
        metadata TEXT NULL,
        score REAL NULL,
        total_questions INTEGER NOT NULL DEFAULT 0,
        correct_answers INTEGER NOT NULL DEFAULT 0,
        processed_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_exam_scans_exam_student ON exam_scans (provas_online_id, student_matricula)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_exam_scans_status ON exam_scans (status)');
};
