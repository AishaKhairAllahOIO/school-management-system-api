<?php

namespace App\Domain\Scheduling\Scoring\Penalties;


class AvoidFirstPeriodPenalty implements PenaltyInterface
{


public function calculate(array $solution):array
{

$penalty=0;


foreach($solution as $entry)
{


if(

$entry['lesson']['avoidFirstPeriod']
&&

$entry['slot']['periodIndex']==1

)
{

$penalty+=10;

}


}



return [

'type'=>'avoid_first_period',

'penalty'=>$penalty

];


}


}
