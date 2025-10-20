<?php

namespace App\Events;

class ExamRegistered
{
    public int $examId;
    public int $courseId;
    public string $name;
    public ?string $description;
    public ?int $timeOpen;
    public ?int $timeClose;
    public ?int $timeLimitMinutes;
    public float $maxGrade;

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
}

