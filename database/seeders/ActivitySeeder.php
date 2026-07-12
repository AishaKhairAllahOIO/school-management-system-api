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
        // 🌟 النشاط الأول: مباراة كرة القدم
        Activity::updateOrCreate(
            // 1. مصفوفة البحث: (لا تكرر النشاط إذا كان موجوداً بنفس الاسم والتاريخ)
            [
                'activity_name' => 'football_match',
                'activity_date' => '2026-09-15',
            ],
            // 2. مصفوفة القيم: (البيانات التي سيتم إضافتها أو تحديثها)
            [
                'grade_level_id' => 1,
                'class_room_id'  => 1,
                'type'           => 'sports',
                'start_time'     => '09:00:00',
                'end_time'       => '11:00:00',
            ]
        );

        // 🌟 النشاط الثاني: ورشة الفنون
        Activity::updateOrCreate(
            // 1. مصفوفة البحث
            [
                'activity_name' => 'art_workshop',
                'activity_date' => '2026-09-18',
            ],
            // 2. مصفوفة القيم
            [
                'grade_level_id' => 1,
                'class_room_id'  => null, // أو يمكنك حذفه إذا كان يقبل null افتراضياً
                'type'           => 'cultural',
                'start_time'     => '10:00:00',
                'end_time'       => '12:00:00',
            ]
        );
    }
}

