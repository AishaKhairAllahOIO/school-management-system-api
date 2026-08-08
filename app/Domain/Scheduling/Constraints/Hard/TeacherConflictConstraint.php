<?php

namespace App\Domain\Scheduling\Constraints\Hard;

use App\Domain\Scheduling\Constraints\ConstraintInterface;
use App\Domain\Scheduling\Algorithms\SearchState;

class TeacherConflictConstraint implements ConstraintInterface
{
    public function passes(array $lesson, array $slot, SearchState $state): bool
    {
        return !$state->teacherBusy(
            $lesson['teacherId'],
            $slot['day'],
            $slot['periodIndex']
        );
    }
}
