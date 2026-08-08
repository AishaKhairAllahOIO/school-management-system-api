<?php

namespace App\Domain\Scheduling\Constraints;

use App\Domain\Scheduling\Constraints\Hard\TeacherConflictConstraint;
use App\Domain\Scheduling\Constraints\Hard\ClassConflictConstraint;

use App\Domain\Scheduling\Constraints\Soft\AvoidFirstPeriodConstraint;
use App\Domain\Scheduling\Constraints\Soft\AvoidLastPeriodConstraint;
use App\Domain\Scheduling\Constraints\Soft\DailyLimitConstraint;
use App\Domain\Scheduling\Constraints\Soft\HeavySubjectLimitConstraint;

class ConstraintFactory
{
    public function makeHard(): array
    {
        return [
            new TeacherConflictConstraint(),
            new ClassConflictConstraint(),
        ];
    }

    public function makeSoft(): array
    {
        return [
            new AvoidFirstPeriodConstraint(),
            new AvoidLastPeriodConstraint(),
            new DailyLimitConstraint(),
            new HeavySubjectLimitConstraint(),
        ];
    }
}
