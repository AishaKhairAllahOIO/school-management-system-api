<?php

namespace Database\Seeders;

use App\Models\StudentMark;
use App\Models\GradeSubject;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StudentMarkSeeder extends Seeder
{
    public function run(): void
    {
        $teacherId = 1;
        $now = Carbon::now();

        // 1. جلب كافة القيود (Enrollments) الموجودة في النظام مهما كان عددها (سواء 3 أو 25 أو أكثر)
        $enrollments = Enrollment::all();

        if ($enrollments->isEmpty()) {
          //  $this->command->error('❌ خطأ: لا توجد قيود طلابية (Enrollments). يرجى تسجيل الطلاب أولاً!');
            return;
        }

        // جلب كافة المواد الدراسية ومكونات التقييم الخاصة بها
        $gradeSubjects = GradeSubject::with('assessmentComponents')->get();

        if ($gradeSubjects->isEmpty()) {
           // $this->command->error('❌ خطأ: لا توجد مواد دراسية أو مكونات تقييم معرفة.');
            return;
        }

        // 2. تنظيف العلامات القديمة لمنع التكرار
        StudentMark::query()->delete();

        $marksToInsert = [];
        $counter = 1;

        foreach ($enrollments as $enrollment) {
            
            // 💡 تنويع مستويات الطلاب بناءً على تسلسلهم في النظام لضمان تنوع نتائج الجلاءات والإحصائيات:
            // - أول 5 طلاب: متفوقون جداً (ينافسون على العشرة الأوائل) -> نسبة 90%
            // - الطلاب من 6 إلى 20: ناجحون طبيعيون -> نسبة 70%
            // - الباقون: حالات رسوب (لاختبار فلتر المواد المرسبة أو المجموع) -> نسبة 35% إلى 45%
            $level = match(true) {
                ($counter <= 5)  => 'top_student',
                ($counter <= 20) => 'regular_passed',
                default          => 'failing_student',
            };

            foreach ($gradeSubjects as $gradeSubject) {
                if ($gradeSubject->assessmentComponents->isEmpty()) {
                    continue;
                }

                foreach ($gradeSubject->assessmentComponents as $component) {
                    
                    $mark = match($level) {
                        'top_student'     => round($component->max_mark * 0.90, 2),
                        'regular_passed'  => round($component->max_mark * 0.70, 2),
                        'failing_student' => $gradeSubject->is_failing_subject 
                            ? round($component->max_mark * 0.35, 2) // رسوب في المادة الأساسية
                            : round($component->max_mark * 0.45, 2), // رسوب في مادة غير مرسبة
                    };

                    $marksToInsert[] = [
                        'enrollment_id'           => $enrollment->id,
                        'assessment_component_id' => $component->id,
                        'teacher_id'              => $teacherId,
                        'mark'                    => $mark,
                        'created_at'              => $now,
                        'updated_at'              => $now,
                    ];
                }
            }

            $counter++;
        }

        // 3. إدخال العلامات دفعة واحدة بأداء عالي جداً (Bulk Insert)
        if (!empty($marksToInsert)) {
            StudentMark::insert($marksToInsert);
            //$this->command->info('✨ تم حقن العلامات بنجاح تام لجميع طلاب المدرسة (' . $enrollments->count() . ' طالباً)!');
        }
    }
}