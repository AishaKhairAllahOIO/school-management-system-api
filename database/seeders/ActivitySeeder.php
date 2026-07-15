<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Activity::updateOrCreate(
            [
                'activity_name' => 'football_match',
                'activity_date' => '2026-09-15',
            ],
            [
                'grade_level_id' => 1,
                'class_room_id'  => 1,
                'type'           => 'sports',
                'start_time'     => '09:00:00',
                'end_time'       => '11:00:00',
                'description'   =>'تقام مبارات كرة قدم بين الطلاب للتشجيع روح التعاون والفريق وتحفيزهم على الانجاز'
            ]
        );

        Activity::updateOrCreate(
            [
                'activity_name' => 'art_workshop',
                'activity_date' => '2026-09-18',
            ],
            [
                'grade_level_id' => 1,
                'class_room_id'  => null,
                'type'           => 'cultural',
                'start_time'     => '10:00:00',
                'end_time'       => '12:00:00',
                'description'  =>'تقام ورشة فنون وانشطة فنية للطلاب بالتاريخ المذكور اعلاه لتنمية حس الفن لدى الطلبة.'

            ]
        );
    }
}

