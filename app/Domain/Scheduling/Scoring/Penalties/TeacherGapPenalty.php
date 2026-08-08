<?php

namespace App\Domain\Scheduling\Scoring\Penalties;

class TeacherGapPenalty implements PenaltyInterface
{
    public function calculate(array $solution): array
    {
        $penalty = 0;
        // The structure will be: $teachers[teacherId][day] = [periodIndex1, periodIndex2, ...]
        $teachers = [];

        // 1. Group by Teacher AND Day (CRITICAL FIX)
        foreach ($solution as $entry) {
            $teacherId = $entry['lesson']['teacherId'];
            $day = $entry['slot']['day'];

            $teachers[$teacherId][$day][] = $entry['slot']['periodIndex'];
        }

        // 2. Calculate gaps day by day for each teacher
        foreach ($teachers as $teacherId => $days) {
            foreach ($days as $day => $periods) {
                sort($periods); // Sort periods sequentially for the day

                for ($i = 1; $i < count($periods); $i++) {
                    // Difference between consecutive periods.
                    // e.g., Period 1 and Period 2 -> 2 - 1 = 1 (No gap)
                    // e.g., Period 1 and Period 3 -> 3 - 1 = 2 (1 gap period)
                    // You used > 2. So a difference of 3 (e.g., period 1 and 4) gives a penalty.
                    if (($periods[$i] - $periods[$i - 1]) > 2) {
                        $penalty += 3;
                    }
                }
            }
        }

        return [
            'type'    => 'teacher_gap',
            'penalty' => $penalty
        ];
    }
}
