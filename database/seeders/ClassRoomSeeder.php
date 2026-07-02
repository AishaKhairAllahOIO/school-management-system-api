<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\GradeLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        // 1. جلب أو إنشاء عام دراسي افتراضي لربط الشعب به
        $currentYear = AcademicYear::firstOrCreate(
            [
                'year_name' => '2026-2027', 
                'start_date' => '2026-09-01', 
                'end_date' => '2027-06-30'
            ]
        );

        // 2. تحديث الحقول لتتوافق مع (grade_id) بدلاً من (grade_level_id)
        $classRooms = [
            ['name' => 'الشعبة الأولى', 'capacity' => 35, 'grade_level_id' => 1],
            ['name' => 'الشعبة الثانية', 'capacity' => 35, 'grade_level_id' => 1],
            ['name' => 'الشعبة الثالثة', 'capacity' => 35, 'grade_level_id' => 2],
            ['name' => 'الشعبة الرابعة', 'capacity' => 35, 'grade_level_id' => 2],
        ];

        // 3. الإدخال مع ربط الشعبة بالعام الدراسي والصف
        foreach ($classRooms as $room) {
            Classroom::updateOrCreate(
                [
                    'name'             => $room['name'],
                    'academic_year_id' => $currentYear->id,
                    'grade_level_id'         => $room['grade_level_id']
                ],
                [
                    'capacity' => $room['capacity']
                ]
            );
        }
    }
}
