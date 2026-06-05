<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\GradeLevel;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // نفترض وجود طالب واحد على الأقل وسنة دراسية واحدة وصف واحد
        $student = Student::first();
        $year = AcademicYear::first();
        $grade = GradeLevel::first();

        if ($student && $year && $grade) {
            Enrollment::updateOrCreate(
                ['student_id' => 1], // لضمان عدم تكرار تسجيل الطالب في نفس السنة
                [
                    'academic_year_id' => $year->id,
                    'grade_level_id' => $grade->id,
                    'enrollment_status' => 'مثبت',
                    'academic_result' => 'قيد الدراسة'
                ]
            );
        }
    }
}
