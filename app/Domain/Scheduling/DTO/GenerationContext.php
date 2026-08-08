<?php

namespace App\Domain\Scheduling\DTO;

class GenerationContext
{
    /**
     * @param TimeSlotDTO[] $timeSlots Array of available time slots
     * @param array[] $lessons Array of individual lessons ready to be assigned
     */
    public function __construct(
        public array $timeSlots,
        public array $lessons,
        public array $classRoomIds,
    ) {}
}
