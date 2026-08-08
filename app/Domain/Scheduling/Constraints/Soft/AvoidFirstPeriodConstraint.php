<?php

namespace App\Domain\Scheduling\Constraints\Soft;

class AvoidFirstPeriodConstraint
{
    public function penalty(array $lesson, array $slot): int
    {
        // Assuming periodIndex starts at 1 for the first period of the day.
        // (Change it to 0 if your system uses zero-based indexing for periods).
        $isFirstPeriod = ($slot['periodIndex'] === 1);

        if ($lesson['avoidFirstPeriod'] && $isFirstPeriod) {
            return 20; // Penalty points
        }

        return 0;
    }
}
