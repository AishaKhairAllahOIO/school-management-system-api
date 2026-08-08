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
        // 1. Mathematics (رياضيات) - 5 حصص
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

        // 2. Physics (فيزياء) - حصتان (2)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 2,
            ],
            [
                'weekly_periods'       => 2,
                'difficulty'           => 'heavy',
                'max_mark'             => 400.00,
                'passing_mark'         => 200.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => true,
            ]
        );

        // 3. Chemistry (كيمياء) - حصتان (2)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 3,
            ],
            [
                'weekly_periods'       => 2,
                'difficulty'           => 'heavy',
                'max_mark'             => 400.00,
                'passing_mark'         => 200.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => true,
            ]
        );

        // 4. Arabic (لغة عربية) - 5 حصص
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 4,
            ],
            [
                'weekly_periods'       => 5,
                'difficulty'           => 'medium',
                'max_mark'             => 600.00,
                'passing_mark'         => 300.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 2,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 5. English (لغة إنجليزية) - 4 حصص
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
                'max_mark'             => 400.00,
                'passing_mark'         => 200.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 6. Islamic Studies (ديانة) - حصتان (2)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 6,
            ],
            [
                'weekly_periods'       => 2,
                'difficulty'           => 'medium',
                'max_mark'             => 200.00,
                'passing_mark'         => 100.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 7. French (لغة فرنسية) - 3 حصص
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 7,
            ],
            [
                'weekly_periods'       => 3,
                'difficulty'           => 'medium',
                'max_mark'             => 300.00,
                'passing_mark'         => 150.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 8. History (تاريخ) - حصتان (2)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 8,
            ],
            [
                'weekly_periods'       => 2,
                'difficulty'           => 'medium',
                'max_mark'             => 200.00,
                'passing_mark'         => 100.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 9. Geography (جغرافيا) - حصتان (2)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 9,
            ],
            [
                'weekly_periods'       => 2,
                'difficulty'           => 'medium',
                'max_mark'             => 200.00,
                'passing_mark'         => 100.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 10. Computer Science (معلوماتية) - حصتان (2)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 10,
            ],
            [
                'weekly_periods'       => 2,
                'difficulty'           => 'medium',
                'max_mark'             => 200.00,
                'passing_mark'         => 100.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 11. Science (علوم) - 3 حصص
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 11,
            ],
            [
                'weekly_periods'       => 3,
                'difficulty'           => 'heavy',
                'max_mark'             => 300.00,
                'passing_mark'         => 150.00,
                'is_failing_subject'   => true,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => false,
                'avoid_last_period'    => false,
            ]
        );

        // 12. Art (فنون) - حصة واحدة (1)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 12,
            ],
            [
                'weekly_periods'       => 1,
                'difficulty'           => 'light',
                'max_mark'             => 100.00,
                'passing_mark'         => 50.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => true, // يفضل ألا تكون الأولى
                'avoid_last_period'    => false,
            ]
        );

        // 13. Music (موسيقى) - حصة واحدة (1)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 13,
            ],
            [
                'weekly_periods'       => 1,
                'difficulty'           => 'light',
                'max_mark'             => 100.00,
                'passing_mark'         => 50.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => true,
                'avoid_last_period'    => false,
            ]
        );

        // 14. Sports (رياضة) - حصة واحدة (1)
        GradeSubject::updateOrCreate(
            [
                'academic_year_id' => 1,
                'semester_id'      => 1,
                'grade_level_id'   => 1,
                'subject_id'       => 14,
            ],
            [
                'weekly_periods'       => 1,
                'difficulty'           => 'light',
                'max_mark'             => 100.00,
                'passing_mark'         => 50.00,
                'is_failing_subject'   => false,
                'weight_in_total'      => 1.00,
                'max_periods_per_day'  => 1,
                'avoid_first_period'   => true, 
                'avoid_last_period'    => false,
            ]
        );
    }
}
