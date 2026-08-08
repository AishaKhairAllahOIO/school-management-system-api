<?php

namespace App\Domain\Scheduling\Constraints\Soft;

class AvoidLastPeriodConstraint
{
    public function penalty(array $lesson, array $slot): int
    {
        // We assume $slot['isLastPeriod'] is a boolean calculated when generating TimeSlots
        // or passed dynamically, instead of hardcoding the number 6.
        $isLastPeriod = $slot['isLastPeriod'] ?? ($slot['periodIndex'] >= 6);

        if ($lesson['avoidLastPeriod'] && $isLastPeriod) {
            return 20; // Penalty points
        }

        return 0;
    }
}
