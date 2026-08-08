<?php

namespace Database\Seeders;

use App\Models\GradeConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GradeConfiguration::updateOrCreate(
            [
                'academic_year_id' => 1,
                'grade_level_id'   => 1,
            ],
            [
                'supervisor_id'             => 10,
                'planned_classrooms_count'  => 4,
                'planned_students_capacity' => 120,
            ]
        );
        GradeConfiguration::updateOrCreate(
            [
                'academic_year_id' => 1,
                'grade_level_id'   => 2,
            ],
            [
                'supervisor_id'             => 10,
                'planned_classrooms_count'  => 3,
                'planned_students_capacity' => 120,
            ]
        );

    }
}
