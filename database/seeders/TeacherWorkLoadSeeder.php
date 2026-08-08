<?php

namespace Database\Seeders;

use App\Models\TeacherWorkload;
use Illuminate\Database\Seeder;

class TeacherWorkLoadSeeder extends Seeder
{
    public function run(): void
    {
        // الأستاذ رقم 1
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 1],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );

        // الأستاذ رقم 8
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 8],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );

        // الأستاذ رقم 10
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 10],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );

        // الأستاذ رقم 11
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 11],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );

        // الأستاذ رقم 12
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 12],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );

        // الأستاذ رقم 13
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 13],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );

        // الأستاذ رقم 14
        TeacherWorkload::updateOrCreate(
            ['academic_year_id' => 1, 'teacher_id' => 14],
            ['assigned_monthly_periods' => 28, 'required_monthly_periods' => 30]
        );
    }
}
