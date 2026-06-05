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

        $classRooms = [
            ['class_number' => 'الشعبة الاولى', 'capacity' => 35, 'grade_level_id' => 1],
            ['class_number' => 'الشعبة الثانية', 'capacity' => 35, 'grade_level_id' => 1],
            ['class_number' => 'الشعبة الثالثة', 'capacity' => 35, 'grade_level_id' => 2],


        ];

        foreach ($classRooms as $room) {
            ClassRoom::updateOrCreate(['class_number' => $room['class_number']], $room);
        }
    }
}
