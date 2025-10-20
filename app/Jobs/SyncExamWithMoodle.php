<?php

namespace App\Jobs;

use App\Models\MoodleSyncLog;
use App\Services\MoodleClient;
use Throwable;

class SyncExamWithMoodle
{
    private int $examId;
    private int $courseId;
    private string $name;
    private ?string $description;
    private ?int $timeOpen;
    private ?int $timeClose;
    private ?int $timeLimitMinutes;
    private float $maxGrade;

    public function __construct(
        int $examId,
        int $courseId,
        string $name,
        ?string $description = null,
        ?int $timeOpen = null,
        ?int $timeClose = null,
        ?int $timeLimitMinutes = null,
        float $maxGrade = 10.0
    ) {
        $this->examId = $examId;
        $this->courseId = $courseId;
        $this->name = $name;
        $this->description = $description;
        $this->timeOpen = $timeOpen;
        $this->timeClose = $timeClose;
        $this->timeLimitMinutes = $timeLimitMinutes;
        $this->maxGrade = $maxGrade;
    }

    public function handle(): void
    {
        $client = MoodleClient::fromEnv();
        $payload = [
            'quizzes' => [
                [
                    'courseid' => $this->courseId,
                    'name' => $this->name,
                    'intro' => $this->description ?? '',
                    'timeopen' => $this->timeOpen ?? 0,
                    'timeclose' => $this->timeClose ?? 0,
                    'timelimit' => $this->timeLimitMinutes ? $this->timeLimitMinutes * 60 : 0,
                    'grade' => $this->maxGrade,
                ],
            ],
        ];

        try {
            $response = $client->call('mod_quiz_create_quizzes', $payload);
            MoodleSyncLog::record(
                'exam',
                (string) $this->examId,
                'create',
                'success',
                $payload,
                $response
            );
        } catch (Throwable $exception) {
            MoodleSyncLog::record(
                'exam',
                (string) $this->examId,
                'create',
                'failed',
                $payload,
                null,
                $exception->getMessage()
            );

            throw $exception;
        }
    }
}

