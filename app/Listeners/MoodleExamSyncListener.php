<?php

namespace App\Listeners;

use App\Events\ExamRegistered;
use App\Jobs\SyncExamWithMoodle;

class MoodleExamSyncListener
{
    public function handle(ExamRegistered $event): void
    {
        $job = new SyncExamWithMoodle(
            $event->examId,
            $event->courseId,
            $event->name,
            $event->description,
            $event->timeOpen,
            $event->timeClose,
            $event->timeLimitMinutes,
            $event->maxGrade
        );

        $job->handle();
    }
}

