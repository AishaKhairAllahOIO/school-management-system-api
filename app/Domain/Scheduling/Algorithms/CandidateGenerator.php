<?php

namespace App\Domain\Scheduling\Algorithms;


class CandidateGenerator
{
    public function generate(
        array $lesson,
        array $slots,
        SearchState $state
    ): array {

        $candidates = [];

        foreach ($slots as $slot) {

            $slotArray = [
                'day' => $slot->day,
                'periodIndex' => $slot->periodIndex,
            ];


            if ($state->canPlace($lesson, $slotArray)) {

                $candidates[] = $slotArray;

            }

        }


        return $candidates;
    }



    public function hasAnyCandidate(
        array $lesson,
        array $slots,
        SearchState $state
    ): bool {


        foreach ($slots as $slot) {


            $slotArray = [
                'day' => $slot->day,
                'periodIndex' => $slot->periodIndex,
            ];


            if ($state->canPlace($lesson,$slotArray)) {

                return true;

            }

        }


        return false;
    }
}
