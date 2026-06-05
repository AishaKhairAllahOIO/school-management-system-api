<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        // جلب السنة الدراسية التي أنشأناها في AcademicYearSeeder
        $academicYear = AcademicYear::where('year_name', '2025-2026')->first();

        if ($academicYear) {
            $semesters = [
                [
                    'semester_name' => Semester::FIRST_TERM,
                    'start_date' => '2025-09-01',
                    'end_date' => '2026-01-15',
                    'academic_year_id' => $academicYear->id
                ],
                [
                    'semester_name' => Semester::SECOUND_TERM,
                    'start_date' => '2026-01-20',
                    'end_date' => '2026-06-30',
                    'academic_year_id' => $academicYear->id
                ]
            ];

            foreach ($semesters as $semester) {
                Semester::updateOrCreate(
                    ['semester_name' => $semester['semester_name'], 'academic_year_id' => $semester['academic_year_id']],
                    $semester
                );
            }
        }
    }
}
