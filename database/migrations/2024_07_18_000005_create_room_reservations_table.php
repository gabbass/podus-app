<?php

use PDO;
use PDOException;

declare(strict_types=1);

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $pdo->exec('CREATE TABLE IF NOT EXISTS room_reservations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            room_id BIGINT UNSIGNED NOT NULL,
            planning_id BIGINT UNSIGNED NOT NULL,
            reserved_by BIGINT UNSIGNED NOT NULL,
            reserved_for BIGINT UNSIGNED NULL,
            status VARCHAR(32) NOT NULL,
            starts_at DATETIME NOT NULL,
            ends_at DATETIME NOT NULL,
            notes TEXT NULL,
            approved_by BIGINT UNSIGNED NULL,
            approved_at DATETIME NULL,
            approval_comment TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_reservation_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            CONSTRAINT fk_reservation_user FOREIGN KEY (reserved_by) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_reservation_reserved_for FOREIGN KEY (reserved_for) REFERENCES users(id) ON DELETE SET NULL,
            CONSTRAINT fk_reservation_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $hasPlanning = $pdo->query("SHOW TABLES LIKE 'planejamento'");
        if ($hasPlanning && $hasPlanning->fetch()) {
            try {
                $pdo->exec('ALTER TABLE room_reservations
                    ADD CONSTRAINT fk_reservation_planning
                    FOREIGN KEY (planning_id) REFERENCES planejamento(id)
                    ON DELETE CASCADE');
            } catch (PDOException $exception) {
                // Ignora se a constraint já existir ou se a tabela não suporta
            }
        }

        $pdo->exec('CREATE INDEX idx_reservation_room_period ON room_reservations (room_id, starts_at, ends_at)');
        $pdo->exec('CREATE INDEX idx_reservation_planning ON room_reservations (planning_id)');

        return;
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS room_reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        room_id INTEGER NOT NULL,
        planning_id INTEGER NOT NULL,
        reserved_by INTEGER NOT NULL,
        reserved_for INTEGER NULL,
        status VARCHAR(32) NOT NULL,
        starts_at DATETIME NOT NULL,
        ends_at DATETIME NOT NULL,
        notes TEXT NULL,
        approved_by INTEGER NULL,
        approved_at DATETIME NULL,
        approval_comment TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
        FOREIGN KEY (reserved_by) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (reserved_for) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
    )');

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reservation_room_period ON room_reservations (room_id, starts_at, ends_at)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reservation_planning ON room_reservations (planning_id)');

    try {
        $exists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='planejamento'");
        if ($exists && $exists->fetch()) {
            $pdo->exec('CREATE TRIGGER IF NOT EXISTS trg_reservations_planning_delete
                AFTER DELETE ON planejamento
                BEGIN
                    DELETE FROM room_reservations WHERE planning_id = OLD.id;
                END');
        }
    } catch (PDOException $exception) {
        // ignora erros de compatibilidade do SQLite antigo
    }
};
