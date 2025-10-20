<?php

use App\Events\ExamRegistered;
use App\Events\GradeReleased;
use App\Listeners\MoodleExamSyncListener;
use App\Listeners\MoodleGradeSyncListener;
use App\Support\EventDispatcher;

$examListener = new MoodleExamSyncListener();
$gradeListener = new MoodleGradeSyncListener();

EventDispatcher::listen(ExamRegistered::class, [$examListener, 'handle']);
EventDispatcher::listen(GradeReleased::class, [$gradeListener, 'handle']);

