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
        
        // إذا لم يكن هناك طلاب أو سنة دراسية رقم 1، أوقف السيدر حتى لا ينهار السيرفر
        if (!$year) {
            return;
        }

        // تسجيل الطالب الأول (بافتراض أنه دفع الرسوم وأصبح enrolled)
        if ($student1) {
            Enrollment::updateOrCreate(
                [
                    // مفاتيح البحث: نمنع تكرار هذا الطالب في هذه السنة تحديداً
                    'student_id' => 1,
                    'academic_year_id' => 1,
                ],
                [
                    'grade_level_id' => 1,
                    'class_room_id' => 1,
                    'enrollment_status' => 'enrolled', // تم التعديل لتطابق الهيكلية
                    'enrollment_date' => now(), // أضفنا تاريخ التسجيل بما أنه مفعل
                ]
            );
        }

        // تسجيل الطالب الثاني (بافتراض أنه سجل ولم يدفع بعد، فحسابه suspended)
        if ($student2) {
            Enrollment::updateOrCreate(
                [
                    'student_id' => 2,
                    'academic_year_id' => 1,
                ],
                [
                    'grade_level_id' => 2,
                    'class_room_id' => 3,
                    'enrollment_status' => 'suspended', // طالب معلق مالياً
                    'enrollment_date' => null, // لم يدفع لذا لا يوجد تاريخ التحاق فعلي
                ]
            );
        }
    }
}