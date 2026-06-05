<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\GradeLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstGrade = GradeLevel::where('grade_name', 'الصف السابع')->first();

        $classRooms = [
            ['class_number' => 'الشعبة الاولى', 'capacity' => 35, 'grade_level_id' => $firstGrade->id],
            ['class_number' => 'الشعبة الثانية', 'capacity' => 35, 'grade_level_id' => $firstGrade->id],
            ['class_number' => 'الشعبة الثالثة', 'capacity' => 35, 'grade_level_id' => $firstGrade->id],

        ];

        foreach ($classRooms as $room) {
            ClassRoom::updateOrCreate(['class_number' => $room['class_number']], $room);
        }
    }
}
