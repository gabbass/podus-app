<?php

namespace Tests;

use App\Support\Session\SessionManager;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ?string $databasePath = null;
    private ?PDO $pdoInstance = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databasePath = tempnam(sys_get_temp_dir(), 'podus_');
        if ($this->databasePath === false) {
            throw new \RuntimeException('Unable to allocate temporary database.');
        }

        // Remove the file created by tempnam, SQLite will create it for us
        if (file_exists($this->databasePath)) {
            unlink($this->databasePath);
        }

        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $this->databasePath;
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $this->databasePath);
    }

    protected function tearDown(): void
    {
        SessionManager::reset();
        $this->pdoInstance = null;

        if ($this->databasePath && file_exists($this->databasePath)) {
            unlink($this->databasePath);
        }

        parent::tearDown();
    }

    protected function pdo(): PDO
    {
        if ($this->pdoInstance === null) {
            $this->pdoInstance = new PDO('sqlite:' . $this->databasePath);
            $this->pdoInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this->pdoInstance;
    }
}
