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
        $classRoom = $grade->classRooms()->first();

        if ($student && $year && $grade && $classRoom) {
            Enrollment::updateOrCreate(
                ['student_id' => 1], // لضمان عدم تكرار تسجيل الطالب في نفس السنة
                [
                    'academic_year_id' => 1,
                    'grade_level_id' => 1,
                    'class_room_id' =>  1 ,
                    'enrollment_status' => 'مثبت',
                    'academic_result' => 'قيد الدراسة'
                ]
            );

            Enrollment::updateOrCreate(
                ['student_id' => 2], // لضمان عدم تكرار تسجيل الطالب في نفس السنة
                [
                    'academic_year_id' => 1,
                    'grade_level_id' => 2,
                    'class_room_id' =>  3,
                    'enrollment_status' => 'مثبت',
                    'academic_result' => 'قيد الدراسة'
                ]
            );
        }
    }
}
