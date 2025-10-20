<?php

namespace App\Listeners;

use App\Events\GradeReleased;
use App\Jobs\SyncGradeWithMoodle;
use App\Models\MoodleSyncLog;

class MoodleGradeSyncListener
{
    public function handle(GradeReleased $event): void
    {
        if ($event->moodleGradeItemId === null || $event->studentMoodleId === null) {
            MoodleSyncLog::record(
                'grade',
                (string) $event->gradeId,
                'update',
                'skipped',
                [
                    'reason' => 'missing_moodle_identifiers',
                    'exam_id' => $event->examId,
                    'student_id' => $event->studentId,
                ]
            );

            return;
        }

        $job = new SyncGradeWithMoodle(
            $event->gradeId,
            $event->moodleGradeItemId,
            $event->studentMoodleId,
            $event->grade,
            $event->feedback
        );

        $job->handle();
    }
}

