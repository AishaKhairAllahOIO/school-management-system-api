<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades=[
            ['id' => 1, 'grade_name' => 'الصف السابع'],
            ['id' => 2, 'grade_name' => 'الصف الثامن'],
            ['id' => 3, 'grade_name' => 'الصف التاسع'],

        ];

        foreach ($grades as $grade) {
           GradeLevel::updateOrCreate(['id' => $grade['id']], ['grade_name' => $grade['grade_name']]);        }
    }
}
