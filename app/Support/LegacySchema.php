<?php

namespace App\Support;

use LegacyConfig;
use PDO;

class LegacySchema
{
    private static ?PDO $pdo = null;
    /** @var array<string,bool> */
    private static array $tables = [];
    /** @var array<string,array<string,bool>> */
    private static array $columns = [];

    private static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = LegacyConfig::createPdo();
        }

        return self::$pdo;
    }

    public static function hasTable(string $table): bool
    {
        if (isset(self::$tables[$table])) {
            return self::$tables[$table];
        }

        $pdo = self::pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1");
            $stmt->execute(['table' => $table]);
            return self::$tables[$table] = (bool) $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table LIMIT 1');
        $stmt->execute(['table' => $table]);

        return self::$tables[$table] = (bool) $stmt->fetchColumn();
    }

    public static function hasColumn(string $table, string $column): bool
    {
        if (! self::hasTable($table)) {
            return false;
        }

        if (isset(self::$columns[$table][$column])) {
            return self::$columns[$table][$column];
        }

        $pdo = self::pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('PRAGMA table_info(' . $table . ')');
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $map = [];
            foreach ($columns as $info) {
                $name = $info['name'] ?? null;
                if (is_string($name)) {
                    $map[$name] = true;
                }
            }
            self::$columns[$table] = $map;
            return isset($map[$column]);
        }

        $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column LIMIT 1');
        $stmt->execute(['table' => $table, 'column' => $column]);
        $exists = (bool) $stmt->fetchColumn();
        self::$columns[$table][$column] = $exists;

        return $exists;
    }
}
