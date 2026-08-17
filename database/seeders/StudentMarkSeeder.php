<?php

namespace Database\Seeders;

use App\Models\StudentMark;
use App\Models\AssessmentComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StudentMarkSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب كافة مكونات التقييم لكل المواد الموجودة في الداتابيز
        $components = AssessmentComponent::all();

        if ($components->isEmpty()) {
            $this->command->warn('⚠️ تنبيه: لا توجد مكونات تقييم مدخلة. يجدر بك تشغيل AssessmentComponentSeeder أولاً!');
            return;
        }

        // 2. 🎮 لوحة التحكم بالرسوب والنجاح للطلاب (Enrollments)
        // يمكنك إضافة أي قيد وتحديد سيناريو النجاح أو الرسوب له بدقة:
        // 'passed' -> سينال العلامة الكاملة في كل المكونات (نجاح باهر)
        // 'failed' -> سينال 20% فقط من العلامة في كل المكونات (رسوب كلي)
        $studentScenarios = [
            1 => 'passed',  // الطالب صاحب القيد 1 -> ناجح
            3 => 'failed',  // الطالب صاحب القيد 3 -> راسب
        ];

        $teacherId = 1;
        $marksToInsert = [];
        $now = Carbon::now();

        // 3. تنظيف العلامات القديمة لمنع التكرار
        StudentMark::whereIn('enrollment_id', array_keys($studentScenarios))->delete();

        foreach ($studentScenarios as $enrollmentId => $scenario) {
            foreach ($components as $component) {
                
                // تحديد نسبة العلامة بناءً على السيناريو (نجاح أو رسوب)
                $multiplier = ($scenario === 'passed') ? 1.0 : 0.20; // 20% رسوب
                $mark = round($component->max_mark * $multiplier, 2);

                $marksToInsert[] = [
                    'enrollment_id'           => $enrollmentId,
                    'assessment_component_id' => $component->id,
                    'teacher_id'              => $teacherId,
                    'mark'                    => $mark,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }
        }

        // إدخال العلامات دفعة واحدة بأداء عالي جداً (Bulk Insert)
        StudentMark::insert($marksToInsert);
        
        $this->command->info('✨ تم توزيع العلامات بنجاح بناءً على سيناريوهات النجاح والرسوب المحددة!');
    }
}