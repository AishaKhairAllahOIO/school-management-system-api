<?php

namespace Database\Seeders;

use App\Models\GradeSubject;
use Illuminate\Database\Seeder;

class GradeSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 1,
            ],
            [
                'weekly_periods'       => 5,
                'difficulty'           => 'heavy',
                'max_mark'             => 600.00,
                'passing_mark'         => 300.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 2,
                'avoid_first_period'   => false,
                'avoid_last_period'    => true,
            ]
        );

        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 5,
            ],
            [
                'weekly_periods'       => 4,
                'difficulty'           => 'medium',
                'max_mark'             => 300.00,
                'passing_mark'         => 150.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );
    }
}
