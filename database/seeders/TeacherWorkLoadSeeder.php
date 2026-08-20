<?php

namespace Database\Seeders;

use App\Models\TeacherWorkload;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class TeacherWorkLoadSeeder extends Seeder
{
    public function run(): void
    {
        // مصفوفة تحتوي على user_id لجميع المعلمين (14 معلم) بناءً على UserSeeder
        $teacherUserIds = [3, 13, 15, 16, 17, 18, 19, 21, 22, 23, 24, 25, 26, 27];

        // جلب staff_id الفعلي المطابق لكل معلم من جدول الموظفين لضمان التناسق
        $teacherStaffIds = Staff::whereIn('user_id', $teacherUserIds)->pluck('id')->toArray();

        foreach ($teacherStaffIds as $staffId) {
            TeacherWorkload::updateOrCreate(
                ['academic_year_id' => 1, 'teacher_id' => $staffId],
                ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
            );
        }
    }
}
