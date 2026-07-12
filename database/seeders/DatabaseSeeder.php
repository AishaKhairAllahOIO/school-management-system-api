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
            AcademicStageSeeder::class,
            SubjectSeeder::class,
            GradeLevelSeeder::class,
            GeneralSettingSeeder::class,
            AcademicSettingSeeder::class,
            GuardianSeeder::class,
            ClassRoomSeeder::class,
            ActivitySeeder::class,
            StudentSeeder::class,
            EnrollmentSeeder::class,
            FinancialTestSeeder::class,
            ScheduleTimeSlotSeeder::class,
            StaffSeeder::class,
            TeacherAssignmentSeeder::class,
            AlertSeeder::class,
            TeacherWorkLoadSeeder::class,
            AnnouncementSeeder::class


        ]);
    }
}
