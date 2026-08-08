<?php

namespace App\Domain\Scheduling\Scoring;


class ScoreResult
{

    public function __construct(

        public int $score,

        public int $penalty,

        public array $details = []

    ){}

}
