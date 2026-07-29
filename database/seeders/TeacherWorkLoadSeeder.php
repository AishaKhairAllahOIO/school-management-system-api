<?php

namespace Database\Seeders;

use App\Models\TeacherWorkload;
use Illuminate\Database\Seeder;

class TeacherWorkLoadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      TeacherWorkload::updateOrCreate(
            [
                'academic_year_id' => 1,
                'teacher_id'       => 9,
            ],
            [
                'assigned_monthly_periods'=>13,
                'required_monthly_periods' => 20,
            ]
        );
    }
}
