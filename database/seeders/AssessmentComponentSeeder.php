<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentComponent;
use App\Models\GradeSubject;

class AssessmentComponentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب كافة المواد الدراسية المعرفة في الصف
        $gradeSubjects = GradeSubject::all();

        if ($gradeSubjects->isEmpty()) {
            $this->command->error('❌ خطأ: يرجى تشغيل GradeSubjectSeeder أولاً لإنشاء المواد!');
            return;
        }

        // تنظيف المكونات القديمة إن وجدت
        AssessmentComponent::query()->delete();

        // 2. الهيكلية المعيارية لمكونات التقييم لكل مادة بناءً على علامتها العظمى (max_mark)
        foreach ($gradeSubjects as $gs) {
            $max = $gs->max_mark; // العلامة العظمى للمادة (مثلاً 600 أو 400 أو 200 أو 100)

            $components = [
                ['type' => 'oral',          'name' => 'الشفوي',            'max_mark' => $max * 0.05, 'weight' => 5.00],   // 5%
                ['type' => 'homework',      'name' => 'الوظائف والواجبات', 'max_mark' => $max * 0.10, 'weight' => 10.00],  // 10%
                ['type' => 'quiz1',         'name' => 'المذاكرة الأولى',   'max_mark' => $max * 0.10, 'weight' => 10.00],  // 10%
                ['type' => 'quiz2',         'name' => 'المذاكرة الثانية',   'max_mark' => $max * 0.10, 'weight' => 10.00],  // 10%
                ['type' => 'participation', 'name' => 'المشاركة والتفاعل', 'max_mark' => $max * 0.05, 'weight' => 5.00],   // 5%
                ['type' => 'exam',          'name' => 'الامتحان النهائي',   'max_mark' => $max * 0.60, 'weight' => 60.00],  // 60%
            ];

            foreach ($components as $component) {
                AssessmentComponent::create([
                    'grade_subject_id'  => $gs->id,
                    'type'              => $component['type'],
                    'name'              => $component['name'],
                    'max_mark'          => $component['max_mark'],
                    'weight_percentage' => $component['weight'],
                ]);
            }
        }

    }
}