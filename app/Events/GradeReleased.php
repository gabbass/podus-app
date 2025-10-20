<?php

namespace App\Events;

class GradeReleased
{
    public int $gradeId;
    public int $examId;
    public int $studentId;
    public float $grade;
    public ?string $feedback;
    public ?int $moodleGradeItemId;
    public ?int $studentMoodleId;

    public function __construct(
        int $gradeId,
        int $examId,
        int $studentId,
        float $grade,
        ?string $feedback = null,
        ?int $moodleGradeItemId = null,
        ?int $studentMoodleId = null
    ) {
        $this->gradeId = $gradeId;
        $this->examId = $examId;
        $this->studentId = $studentId;
        $this->grade = $grade;
        $this->feedback = $feedback;
        $this->moodleGradeItemId = $moodleGradeItemId;
        $this->studentMoodleId = $studentMoodleId;
    }
}

