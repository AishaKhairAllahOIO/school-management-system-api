<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssessmentComponent;


class AssessmentComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components1 = [
            ['type' => 'oral', 'name' => 'الشفوي', 'max_mark' => 30, 'weight' => 5.00],
            ['type' => 'homework', 'name' => 'الوظائف والواجبات', 'max_mark' => 60, 'weight' => 10.00],
            ['type' => 'quiz1', 'name' => 'المذاكرة الأولى', 'max_mark' => 60, 'weight' => 10.00],
            ['type' => 'quiz2', 'name' => 'المذاكرة الثانية', 'max_mark' => 60, 'weight' => 10.00],
            ['type' => 'participation', 'name' => 'المشاركة والتفاعل', 'max_mark' => 30, 'weight' => 5.00],
            ['type' => 'exam', 'name' => 'الامتحان النهائي', 'max_mark' => 360, 'weight' => 60.00],
        ];

        foreach ($components1 as $component) {
            AssessmentComponent::updateOrCreate(
                [
                    'grade_subject_id' => 1,
                    'type' => $component['type'],
                ],
                [
                    'name' => $component['name'],
                    'max_mark' => $component['max_mark'],
                    'weight_percentage' => $component['weight'],
                ]
            );
        }
        $components2 = [
            ['type' => 'oral', 'name' => 'الشفوي', 'max_mark' => 10, 'weight' => 5.00],
            ['type' => 'homework', 'name' => 'الوظائف والواجبات', 'max_mark' => 20, 'weight' => 10.00],
            ['type' => 'quiz1', 'name' => 'المذاكرة الأولى', 'max_mark' => 20, 'weight' => 10.00],
            ['type' => 'quiz2', 'name' => 'المذاكرة الثانية', 'max_mark' => 20, 'weight' => 10.00],
            ['type' => 'participation', 'name' => 'المشاركة والتفاعل', 'max_mark' => 10, 'weight' => 5.00],
            ['type' => 'exam', 'name' => 'الامتحان النهائي', 'max_mark' => 120, 'weight' => 60.00],
        ];

        foreach ($components2 as $component) {
            AssessmentComponent::updateOrCreate(
                [
                    'grade_subject_id' => 2,
                    'type' => $component['type'],
                ],
                [
                    'name' => $component['name'],
                    'max_mark' => $component['max_mark'],
                    'weight_percentage' => $component['weight'],
                ]
            );
        }
    }
}
