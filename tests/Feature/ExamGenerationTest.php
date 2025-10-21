<?php

namespace Tests\Feature;

use App\Jobs\SyncExamWithMoodle;
use App\Services\MoodleClient;
use PDO;
use Tests\TestCase;

class ExamGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $pdo = $this->pdo();
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
    }

    public function testExamSyncCreatesSuccessLog(): void
    {
        $client = new class extends MoodleClient {
            public array $calls = [];

            public function __construct()
            {
                parent::__construct('https://example.com', 'token');
            }

            public function call(string $function, array $params = []): array
            {
                $this->calls[] = [$function, $params];

                return ['quizzes' => [['id' => 321]]];
            }
        };

        $job = new SyncExamWithMoodle(
            examId: 55,
            courseId: 90,
            name: 'Prova Bimestral',
            description: 'Avaliação de matemática',
            timeOpen: 1700000000,
            timeClose: 1700003600,
            timeLimitMinutes: 45,
            maxGrade: 10.0,
            client: $client
        );

        $job->handle();

        $log = $this->pdo()->query('SELECT * FROM moodle_sync_logs')->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($log);
        $this->assertSame('exam', $log['entity_type']);
        $this->assertSame('success', $log['status']);
        $this->assertSame('55', $log['entity_id']);
        $this->assertNull($log['error_message']);
    }
}
