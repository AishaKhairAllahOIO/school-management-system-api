<?php

namespace Database\Seeders;

use App\Models\Homework;
use Illuminate\Database\Seeder;

class HomeworkSeeder extends Seeder
{
    public function run(): void
    {
        $hw1 = Homework::updateOrCreate(
            [
                'grade_subject_id' => 1,
                'teacher_id'       => 9,
                'title'            => 'حل تمارين الهندسة - الدرس الثاني',
            ],
            [
                'description'      => 'يرجى حل التمارين أ، ب من الصفحة 20 على دفتر الوظائف بوضوح وتنظيم.',
                'due_date'         => '2026-07-28',
            ]
        );
        $hw1->classRooms()->syncWithoutDetaching([1, 2]);


        $hw2 = Homework::updateOrCreate(
            [
                'grade_subject_id' => 2,
                'teacher_id'       => 9,
                'title'            => 'حل تمارين اللغة الانكليزية - الدرس الرابع', 
            ],
            [
                'description'      => 'يرجى حل تمارين الصفحة 55 على كتاب الانشطة بوضوح وتنظيم.',
                'due_date'         => '2026-07-30',
            ]
        );
        $hw2->classRooms()->syncWithoutDetaching([1]);


        $hw3 = Homework::updateOrCreate(
            [
                'grade_subject_id' => 2,
                'teacher_id'       => 9,
                'title'            => 'حل تمارين اللغة الانكليزية - الدرس الخامس',
            ],
            [
                'description'      => 'يرجى حل تمارين الصفحة 64 على كتاب الانشطة بوضوح وتنظيم.',
                'due_date'         => '2026-07-30',
            ]
        );
        $hw3->classRooms()->syncWithoutDetaching([2, 3]);


        $hw4 = Homework::updateOrCreate(
            [
                'grade_subject_id' => 2,
                'teacher_id'       => 9,
                'title'            => 'حل تمارين اللغة الانكليزية - الدرس السادس',
            ],
            [
                'description'      => 'يرجى حل تمارين الصفحة 77 على كتاب الانشطة بوضوح وتنظيم.',
                'due_date'         => '2026-07-30',
            ]
        );
        $hw4->classRooms()->syncWithoutDetaching([1, 2, 3]);
    }
}
