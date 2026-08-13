<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use Illuminate\Database\Seeder;

class ClassRoomSeeder extends Seeder
{
    public function run(): void
    {
        $classRooms = [
            ['name' => 'الشعبة 1', 'grade_level_id' => 1],
            ['name' => 'الشعبة 2', 'grade_level_id' => 1],
            ['name' => 'الشعبة 3', 'grade_level_id' => 1],
            ['name' => 'الشعبة 4', 'grade_level_id' => 1],
            ['name' => 'الشعبة 5', 'grade_level_id' => 1],
            ['name' => 'الشعبة 1', 'grade_level_id' => 2]

        ];

        foreach ($classRooms as $room) {
            ClassRoom::updateOrCreate(
                [
                    'name' => $room['name'],
                    'academic_year_id' => 1,
                    'grade_level_id' => $room['grade_level_id']
                ],
                ['capacity' => 35]
            );
        }
    }
}
