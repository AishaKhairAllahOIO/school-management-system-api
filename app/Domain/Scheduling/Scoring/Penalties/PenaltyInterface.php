<?php

namespace App\Domain\Scheduling\Scoring\Penalties;


interface PenaltyInterface
{


public function calculate(array $solution):array;


}
