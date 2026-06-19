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
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            AcademicYearSeeder::class,
            SemesterSeeder::class,
            GradeLevelSeeder::class,
            GuardianSeeder::class,
            ClassRoomSeeder::class,
            ActivitySeeder::class,
            StudentSeeder::class,
            EnrollmentSeeder::class,
            ScheduleTimeSlotSeeder::class,
            StaffSeeder::class,


        ]);
    }
}
