<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentAttendanceSetting;

class StudentAttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentAttendanceSetting::updateOrCreate(
            ['semester_id' => 1],
            [
                'working_days' => 90, // عدد أيام الدوام
                'required_attendance_percentage' => 85.00, // نسبة الحضور المطلوبة (يعني مسموح غياب 15% = 13 يوم)
            ]
        );
    }
}
