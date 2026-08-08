<?php

namespace App\Domain\Scheduling\Constraints;

use App\Domain\Scheduling\Algorithms\SearchState; // Added import

interface ConstraintInterface
{
    public function passes(
        array $lesson,
        array $slot,
        SearchState $state // Fixed: Strict Type Hinting
    ): bool;
}
