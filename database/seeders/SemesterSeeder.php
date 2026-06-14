<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        Semester::updateOrCreate([
            'semester_name' => Semester::FIRST_TERM,
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-15',
            'academic_year_id' => 1
        ]);

        Semester::updateOrCreate([
            'semester_name' => Semester::SECOUND_TERM,
            'start_date' => '2026-01-20',
            'end_date' => '2026-06-30',
            'academic_year_id' => 1
        ]);
    }
}
