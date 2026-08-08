<?php

namespace App\Domain\Scheduling\Scoring\Penalties;


class AvoidLastPeriodPenalty implements PenaltyInterface
{


public function calculate(array $solution):array
{

$penalty=0;


foreach($solution as $entry)
{


if(

$entry['lesson']['avoidLastPeriod']

&&

$entry['slot']['periodIndex']>=6

)
{

$penalty+=10;

}


}



return [

'type'=>'avoid_last_period',

'penalty'=>$penalty

];


}


}
