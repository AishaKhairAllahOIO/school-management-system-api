<?php

namespace App\Domain\Scheduling\Constraints\Soft;

use App\Domain\Scheduling\Algorithms\SearchState;

class DailyLimitConstraint
{
    public function penalty(array $lesson, array $slot, SearchState $state): int
    {
        $count = $state->subjectCountInDay(
            $lesson['classRoomId'],
            $lesson['subjectId'],
            $slot['day']
        );

        $maxPeriods = $lesson['maxPeriodsPerDay'] ?? 1;

        if ($count >= $maxPeriods) {
            return 100;
        }

        return 0;
    }
}
