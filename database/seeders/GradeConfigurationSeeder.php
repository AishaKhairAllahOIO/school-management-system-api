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
                'planned_classrooms_count'  => 8,
                'planned_students_capacity' => 300,
            ]
        );
        GradeConfiguration::updateOrCreate(
            [
                'academic_year_id' => 1,
                'grade_level_id'   => 2,
            ],
            [
                'supervisor_id'             => 11,
                'planned_classrooms_count'  => 5,
                'planned_students_capacity' => 200,
            ]
        );

    }
}
