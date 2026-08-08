<?php

namespace App\Domain\Scheduling\Builders;

use App\Domain\Scheduling\DTO\LessonRequirement;
use Exception;
use Illuminate\Support\Collection;

class LessonRequirementBuilder
{
    /**
     * @param Collection|array $assignments
     */
    public function build($assignments): array
    {
        $result = [];

        foreach ($assignments as $assignment) {
            $gradeSubject = $assignment->gradeSubject;

            // Bug Fix: Protect against missing database relationships (GIGO prevention)
            if (!$gradeSubject) {
                throw new Exception(
                    "Missing GradeSubject for Assignment ID: {$assignment->id}. Please check your database integrity."
                );
            }

            $result[] = new LessonRequirement(
                assignmentId: $assignment->id,
                teacherId: $assignment->teacher_id,
                classRoomId: $assignment->class_room_id,
                gradeSubjectId: $gradeSubject->id,
                subjectId: $gradeSubject->subject_id,
                weeklyPeriods: $gradeSubject->weekly_periods,
                maxPeriodsPerDay: $gradeSubject->max_periods_per_day ?? 1,
                difficulty: $gradeSubject->difficulty ?? 'normal',
                avoidFirstPeriod: $gradeSubject->avoid_first_period ?? false,
                avoidLastPeriod: $gradeSubject->avoid_last_period ?? false
            );
        }

        return $result;
    }
}
