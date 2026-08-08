<?php

namespace App\Services\Schedule;

use Carbon\Carbon;

class PeriodTimeCalculator
{

    public function calculate(int $periodIndex, array $scheduleSettings): array
    {
        $startTime = Carbon::createFromFormat('H:i', $scheduleSettings['dayStartTime']);
        $duration = (int) $scheduleSettings['periodDurationMinutes'];
        $breaks = collect($scheduleSettings['breaks'] ?? []);

        for ($i = 1; $i < $periodIndex; $i++) {
            $startTime->addMinutes($duration);

            $break = $breaks->firstWhere('afterPeriodIndex', $i);
            if ($break) {
                $startTime->addMinutes((int) $break['durationMinutes']);
            }
        }

        $endTime = (clone $startTime)->addMinutes($duration);

        return [
            'start_time' => $startTime->format('H:i'),
            'end_time'   => $endTime->format('H:i'),
        ];
    }
}
