<?php

namespace App\Domain\Scheduling\Scoring\Penalties;


class HeavySubjectPenalty implements PenaltyInterface
{


    public function calculate(array $solution): array
    {


        $penalty = 0;


        foreach ($solution as $entry) {


            if (

                $entry['lesson']['difficulty']
                ==
                'heavy'

                &&

                $entry['slot']['periodIndex'] >= 6

            ) {

                $penalty += 5;

            }


        }



        return [

            'type' => 'heavy_subject',

            'penalty' => $penalty

        ];


    }


}
