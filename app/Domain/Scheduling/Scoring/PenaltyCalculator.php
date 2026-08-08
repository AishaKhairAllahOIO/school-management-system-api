<?php

namespace App\Domain\Scheduling\Scoring;

use App\Domain\Scheduling\Scoring\Penalties\PenaltyInterface;

class PenaltyCalculator
{
    /** @var PenaltyInterface[] */
    private array $rules;

    // Injecting rules through the constructor makes the class open for extension
    // You can bind this in a Laravel Service Provider
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function calculate(array $solution): array
    {
        $total = 0;
        $details = [];

        foreach ($this->rules as $rule) {
            $result = $rule->calculate($solution);

            $total += $result['penalty'];
            $details[] = $result;
        }

        return [
            'penalty' => $total,
            'details' => $details
        ];
    }
}
