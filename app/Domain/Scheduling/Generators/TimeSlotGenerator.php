<?php

namespace App\Domain\Scheduling\Generators;

use App\Domain\Scheduling\DTO\TimeSlotDTO;
use InvalidArgumentException; 

class TimeSlotGenerator
{
    /**
     * @param array $settings
     * @return TimeSlotDTO[]
     */
    public function generate(array $settings): array
    {
        // Guard Clause 1: Check if workingDays exists and is valid
        if (!isset($settings['workingDays']) || !is_array($settings['workingDays'])) {
            throw new InvalidArgumentException(
                "Invalid schedule settings: 'workingDays' array is missing or invalid."
            );
        }

        $slots = [];

        foreach ($settings['workingDays'] as $day) {

            // Guard Clause 2: Check if required keys for each day exist
            if (!isset($day['day']) || !isset($day['periodsCount'])) {
                throw new InvalidArgumentException(
                    "Invalid day configuration: Each working day must contain 'day' and 'periodsCount' keys."
                );
            }

            $periodsCount = (int) $day['periodsCount'];

            for ($i = 1; $i <= $periodsCount; $i++) {
                $slots[] = new TimeSlotDTO(
                    $day['day'],
                    $i
                );
            }
        }

        return $slots;
    }
}
