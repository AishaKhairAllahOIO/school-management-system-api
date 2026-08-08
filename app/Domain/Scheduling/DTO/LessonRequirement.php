<?php

namespace App\Domain\Scheduling\DTO;

class LessonRequirement
{
    public function __construct(
        public int $teacherId,
        public int $classRoomId,
        public int $assignmentId,
        public int $gradeSubjectId,
        public int $subjectId,
        public int $weeklyPeriods,
        public int $maxPeriodsPerDay = 1,
        public string $difficulty = 'normal',
        public bool $avoidFirstPeriod = false,
        public bool $avoidLastPeriod = false
    ) {}
}
