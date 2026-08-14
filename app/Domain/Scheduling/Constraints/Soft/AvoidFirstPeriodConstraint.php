<?php

namespace App\Domain\Scheduling\Constraints\Soft;

class AvoidFirstPeriodConstraint
{
    public function penalty(array $lesson, array $slot): int
    {
        $isFirstPeriod = ($slot['periodIndex'] === 1);

        if ($lesson['avoidFirstPeriod'] && $isFirstPeriod) {
            return 20; 
        }

        return 0;
    }
}
