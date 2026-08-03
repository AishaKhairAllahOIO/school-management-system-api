<?php

namespace Database\Seeders;

use App\Enums\AcademicStageType;
use App\Models\GradeLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicStage;
use App\Enums\GradeName;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
    {
      

        // 2. تجهيز بيانات الصفوف بالتوافق مع الـ Types الجديدة
        $grades = [
            [
                'id' => 1, 
                'name' => GradeName::SEVENTH, 
                'level' => 7, 
                'is_graduation_grade' => false
            ],
            [
                'id' => 2, 
                'name' => GradeName::EIGHTH, 
                'level' => 8, 
                'is_graduation_grade' => false
            ],
            // [
            //     'id' => 3, 
            //     'name' => GradeName::NINTH, 
            //     'level' => 9, 
            //     'is_graduation_grade' => true // التاسع هو صف التخرج للمرحلة الإعدادية
            // ],
        ];

        // 3. إدخال أو تحديث البيانات
        foreach ($grades as $grade) {
            GradeLevel::updateOrCreate(
                ['id' => $grade['id'],
                'name' => $grade['name']], 
                [
                    'academic_stage_id'   => 1 ,
                    'name'                => $grade['name'],
                    'level'               => $grade['level'],
                    'is_graduation_grade' => $grade['is_graduation_grade']
                ]
            );
        }
    }
}
