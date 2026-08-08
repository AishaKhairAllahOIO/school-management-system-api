<?php

namespace App\Domain\Scheduling\Services;


class LessonExpander
{


    public function expand(array $requirements): array
    {

        $lessons = [];


        foreach ($requirements as $requirement) {


            for (
                $i = 1;
                $i <= $requirement->weeklyPeriods;
                $i++
            ) {


                $lessons[] = [

                    'id' =>
                        uniqid('lesson_'),


                    'teacherId' =>
                        $requirement->teacherId,


                    'classRoomId' =>
                        $requirement->classRoomId,
                        
                    'assignmentId' =>
                        $requirement->assignmentId,


                    'gradeSubjectId' =>
                        $requirement->gradeSubjectId,


                    'subjectId' =>
                        $requirement->subjectId,


                    'difficulty' =>
                        $requirement->difficulty,


                    'maxPeriodsPerDay' =>
                        $requirement->maxPeriodsPerDay,


                    'avoidFirstPeriod' =>
                        $requirement->avoidFirstPeriod,


                    'avoidLastPeriod' =>
                        $requirement->avoidLastPeriod,

                ];


            }

        }


        return $lessons;


    }


}
