<?php

namespace Database\Seeders;

use App\Models\Homework;
use Illuminate\Database\Seeder;

class HomeworkSeeder extends Seeder
{
    public function run(): void
    {
        // 1. الوظيفة الأولى (مادة 1) - نربطها مثلاً بالشعبة رقم 1 و 2
        $hw1 = Homework::updateOrCreate(
            [
                'grade_subject_id' => 1,
                'teacher_id'       => 9,
                'title'            => 'حل تمارين الهندسة - الدرس الثاني', // 👈 تم إزالة علامة التنصيص الزائدة هنا وضمه لشرط البحث
            ],
            [
                'description'      => 'يرجى حل التمارين أ، ب من الصفحة 20 على دفتر الوظائف بوضوح وتنظيم.',
                'due_date'         => '2026-07-28',
            ]
        );
        // 🎯 ربط الوظيفة بالشعب (أرقام الشعب المسندة لهذا المعلم)
        $hw1->classRooms()->syncWithoutDetaching([1, 2]);


        // 2. الوظيفة الثانية (مادة 2 - الدرس الرابع)
        $hw2 = Homework::updateOrCreate(
            [
                'grade_subject_id' => 2,
                'teacher_id'       => 9,
                'title'            => 'حل تمارين اللغة الانكليزية - الدرس الرابع', // 👈 أضفنا العنوان لشرط البحث لمنع الكتابة فوق السطر السابق
            ],
            [
                'description'      => 'يرجى حل تمارين الصفحة 55 على كتاب الانشطة بوضوح وتنظيم.',
                'due_date'         => '2026-07-30',
            ]
        );
        $hw2->classRooms()->syncWithoutDetaching([1]); // توجيهها للشعبة رقم 1 فقط مثلاً


        // 3. الوظيفة الثالثة (مادة 2 - الدرس الخامس)
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


        // 4. الوظيفة الرابعة (مادة 2 - الدرس السادس)
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
