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
            ['id' => 4, 'grade_name' => 'الصف العاشر'],
            ['id' => 5, 'grade_name' => 'الصف الحادي عشر'],
            ['id' => 6, 'grade_name' => 'الصف الثاني عشر'],
        ];

        foreach ($grades as $grade) {
            GradeLevel::updateOrCreate($grade);
        }
    }
}
