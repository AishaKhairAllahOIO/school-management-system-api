<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ClassRoom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classRoom = ClassRoom::with('gradeLevel')->firstOrFail();

        $now = now();

        Activity::insert([
            [
                'grade_level_id' => 1,
                'class_room_id'  => 1,
                'type'           => 'sports',
                'activity_name'           => 'football_match',
                'activity_date'           => '2026-09-15',
                'start_time'     => '09:00:00',
                'end_time'       => '11:00:00',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'grade_level_id' => 2,
                'class_room_id'  => 3,
                'type'           => 'cultural',
                'activity_name'           => 'art_workshop',
                'activity_date'           => '2026-09-18',
                'start_time'     => '10:00:00',
                'end_time'       => '12:00:00',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ]);
    }
}
