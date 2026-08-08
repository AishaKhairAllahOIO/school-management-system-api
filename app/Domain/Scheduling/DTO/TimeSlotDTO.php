<?php

namespace App\Domain\Scheduling\DTO;

class TimeSlotDTO
{
    public function __construct(
        public string $day,
        public int $periodIndex
    ) {}
}
