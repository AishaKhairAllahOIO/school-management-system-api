<?php

namespace App\Domain\Scheduling\Constraints\Hard;

use App\Domain\Scheduling\Constraints\ConstraintInterface;
use App\Domain\Scheduling\Algorithms\SearchState;

class ClassConflictConstraint implements ConstraintInterface
{
    public function passes(array $lesson, array $slot, SearchState $state): bool
    {
        return !$state->classBusy(
            $lesson['classRoomId'],
            $slot['day'],
            $slot['periodIndex']
        );
    }
}
