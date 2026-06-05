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
            ['grade_name'=>'الصف السابع'],
            ['grade_name'=>'الصف الثامن'],
            ['grade_name'=>'الصف التاسع'],
            ['grade_name'=>'الصف العاشر'],
            ['grade_name'=>'الصف الحادي عشر'],
            ['grade_name'=>'الصف الثاني عشر'],
        ];

        foreach ($grades as $grade) {
            GradeLevel::updateOrCreate($grade);
        }
    }
}
