<?php

namespace App\Domain\Scheduling\Constraints\Soft;

use App\Domain\Scheduling\Algorithms\SearchState;

class HeavySubjectLimitConstraint
{
    public function penalty(array $lesson, array $slot, SearchState $state): int
    {
        if (!isset($lesson['difficulty']) || $lesson['difficulty'] !== 'heavy') {
            return 0;
        }

        $heavyCount = $state->heavySubjectCountInDay($lesson['classRoomId'], $slot['day']);

        // إذا كان سيصبح المادة الثقيلة الثالثة في اليوم، نعطيه عقوبة عالية ليتجنبه قدر الإمكان
        if ($heavyCount >= 2) {
            return 50;
        }

        return 0;
    }
}
