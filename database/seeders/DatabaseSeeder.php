<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            AcademicYearSeeder::class, // ضروري قبل التسجيل
            SemesterSeeder::class,     // ضروري قبل التسجيل
            GradeLevelSeeder::class,  // ضروري قبل التسجيل
            GuardianSeeder::class,      // ينشئ سجل الوصي في جدول الأوصياء بناءً على المستخدمين الذين لديهم دور "ولي أمر"
            ClassRoomSeeder::class,
            StudentSeeder::class,      // ينشئ سجل الطالب في جدول الطلاب
            EnrollmentSeeder::class,
            ScheduleTimeSlotSeeder::class,


        ]);
    }
}
