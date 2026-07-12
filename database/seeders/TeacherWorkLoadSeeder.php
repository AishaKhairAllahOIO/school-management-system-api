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
                'staff_id'         => 1,
                'academic_year_id' => 1,
                'semester_id'      => 1,
            ],
            [
                'required_monthly_periods' => 10,
            ]
        );
    }
}
