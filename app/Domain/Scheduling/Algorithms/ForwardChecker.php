<?php

namespace App\Domain\Scheduling\Algorithms;


class ForwardChecker
{

    public function check(
        array $remainingLessons,
        array $slots,
        SearchState $state,
        CandidateGenerator $generator
    ): bool {


        foreach ($remainingLessons as $lesson) {


            if(
                !$generator->hasAnyCandidate(
                    $lesson,
                    $slots,
                    $state
                )
            )
            {
                return false;
            }


        }


        return true;
    }

}
