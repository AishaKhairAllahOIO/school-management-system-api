<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\ClassRoom;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {

        Enrollment::updateOrCreate(
            [
                'student_id' => 1,
                'academic_year_id' => 1,
            ],
            [
                'grade_level_id' => 1,
                'class_room_id' => 1,
                'enrollment_status' => 'suspended',
                'enrollment_date' => now(),
            ]
        );



        Enrollment::updateOrCreate(
            [
                'student_id' => 2,
                'academic_year_id' => 1,
            ],
            [
                'grade_level_id' => 1,
                'class_room_id' => 2,
                'enrollment_status' => 'enrolled',
                'enrollment_date' => now(),
            ]
        );

        Enrollment::updateOrCreate(
            [
                'student_id' => 3,
                'academic_year_id' => 1,
            ],
            [
                'grade_level_id' => 1,
                'class_room_id' => 1,
                'enrollment_status' => 'suspended',
                'enrollment_date' => now(),
            ]
        );
    }
}

