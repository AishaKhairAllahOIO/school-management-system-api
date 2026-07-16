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
        // 1. التحقق الفعلي من وجود البيانات الأساسية قبل زراعة التسجيل
        $student1 = Student::find(1);
        $student2 = Student::find(2);
        $year = AcademicYear::find(1);

        if (!$year) {
            return;
        }

        if ($student1) {
            Enrollment::updateOrCreate(
                [
                    'student_id' => 1,
                    'academic_year_id' => 1,
                ],
                [
                    'grade_level_id' => 1,
                    'class_room_id' => 1,
                    'enrollment_status' => 'enrolled',
                    'enrollment_date' => now(),
                ]
            );
        }

        if ($student2) {
            Enrollment::updateOrCreate(
                [
                    'student_id' => 2,
                    'academic_year_id' => 1,
                ],
                [
                    'grade_level_id' => 2,
                    'class_room_id' => 3,
                    'enrollment_status' => 'enrolled',
                    'enrollment_date' => now(),
                ]
            );
        }
    }
}
