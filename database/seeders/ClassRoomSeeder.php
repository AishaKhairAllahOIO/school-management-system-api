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

        $classRooms = [
            ['name' => 'الشعبة 1', 'capacity' => 35, 'grade_level_id' => 1],
            ['name' => 'الشعبة 2', 'capacity' => 35, 'grade_level_id' => 1],
            ['name' => 'الشعبة 3', 'capacity' => 35, 'grade_level_id' => 2],
            ['name' => 'الشعبة 4', 'capacity' => 35, 'grade_level_id' => 2],
        ];

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
        $classRooms = [
            ['name' => 'الشعبة 1', 'capacity' => 35, 'grade_level_id' => 1],
            ['name' => 'الشعبة 2', 'capacity' => 35, 'grade_level_id' => 1],
            ['name' => 'الشعبة 3', 'capacity' => 35, 'grade_level_id' => 2],
            ['name' => 'الشعبة 4', 'capacity' => 35, 'grade_level_id' => 2],
        ];

        foreach ($classRooms as $room) {
            Classroom::updateOrCreate(
                [
                    'name'             => $room['name'],
                    'academic_year_id' => 1,
                    'grade_level_id'         => $room['grade_level_id']
                ],
                [
                    'capacity' => $room['capacity']
                ]
            );
        }
    }
}
