<?php

namespace Database\Seeders;

use App\Models\TeacherAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TeacherAssignment::updateOrCreate(
            [
                'grade_subject_id' => 1,
                'class_room_id'    => 1,
            ],
            [
                'academic_year_id' => 1, 
                'semester_id'      => 1, 
                'teacher_id'       => 1, 
            ]
        );

        TeacherAssignment::updateOrCreate(
            [
                'grade_subject_id' => 2,
                'class_room_id'    => 1,
            ],
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'teacher_id'       => 1,
            ]
        );

        TeacherAssignment::updateOrCreate(
            [
                'grade_subject_id' => 2,
                'class_room_id'    => 2,
            ],
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'teacher_id'       => 1,
            ]
        );
    }
}
