<?php

namespace App\Jobs;

use App\Models\MoodleSyncLog;
use App\Services\MoodleClient;
use Throwable;

class SyncGradeWithMoodle
{
    private int $gradeId;
    private int $moodleGradeItemId;
    private int $studentMoodleId;
    private float $grade;
    private ?string $feedback;

    public function __construct(int $gradeId, int $moodleGradeItemId, int $studentMoodleId, float $grade, ?string $feedback = null)
    {
        $this->gradeId = $gradeId;
        $this->moodleGradeItemId = $moodleGradeItemId;
        $this->studentMoodleId = $studentMoodleId;
        $this->grade = $grade;
        $this->feedback = $feedback;
    }

    public function handle(): void
    {
        $client = MoodleClient::fromEnv();
        $payload = [
            'updates' => [
                [
                    'itemid' => $this->moodleGradeItemId,
                    'grades' => [
                        [
                            'userid' => $this->studentMoodleId,
                            'grade' => $this->grade,
                            'str_feedback' => $this->feedback ?? '',
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = $client->call('core_grades_update_grades', $payload);
            MoodleSyncLog::record(
                'grade',
                (string) $this->gradeId,
                'update',
                'success',
                $payload,
                $response
            );
        } catch (Throwable $exception) {
            MoodleSyncLog::record(
                'grade',
                (string) $this->gradeId,
                'update',
                'failed',
                $payload,
                null,
                $exception->getMessage()
            );

            throw $exception;
        }
    }
}

