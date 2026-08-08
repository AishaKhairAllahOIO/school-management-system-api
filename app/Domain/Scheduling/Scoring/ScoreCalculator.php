<?php

namespace App\Domain\Scheduling\Scoring;

class ScoreCalculator
{
    public function __construct(
        private PenaltyCalculator $penaltyCalculator
    ) {}

    public function calculate(array $solution): ScoreResult
    {
        $result = $this->penaltyCalculator->calculate($solution);

        $penalty = $result['penalty'];

        // Maximum score is 100, minimum score is 0
        $score = max(0, 100 - $penalty);

        return new ScoreResult(
            score: $score,
            penalty: $penalty,
            details: $result['details']
        );
    }
}
