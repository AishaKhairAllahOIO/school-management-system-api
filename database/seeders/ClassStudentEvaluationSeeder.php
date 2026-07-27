<?php

namespace Database\Seeders;

use App\Models\ClassStudentEvaluation;
use Illuminate\Database\Seeder;

class ClassStudentEvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الثوابت المطلوبة حسب طلبك
        $teacherId = 9;
        $enrollmentIds = [2, 3];

        // رقم المادة الدراسية (تأكد أن ID رقم 1 موجود في جدول grade_subjects عندك، أو غيَره)
        $gradeSubjectId = 1;

        // جميع أنواع التقييمات مع ملاحظة واقعية لكل تقييم
        $evaluationsData = [
            'excellent' => 'أداء ممتاز ومشاركة فعالة جداً خلال الحصة.',
            'very_good' => 'أداء جيد جداً، يستوعب الدروس بسرعة وبدقة.',
            'good'      => 'أداء جيد، لكن يحتاج لمزيد من التركيز أحياناً.',
            'average'   => 'أداء متوسط، يتطلب متابعة أكبر للوظائف المنزلية.',
            'weak'      => 'أداء ضعيف، يحتاج إلى دعم مكثف ومتابعة عاجلة من ولي الأمر.',
        ];

        foreach ($enrollmentIds as $enrollmentId) {
            foreach ($evaluationsData as $rating => $note) {
                ClassStudentEvaluation::create([
                    'teacher_id'       => $teacherId,
                    'enrollment_id'    => $enrollmentId,
                    'grade_subject_id' => $gradeSubjectId,
                    'rating'           => $rating,
                    'notes'            => $note,
                ]);
            }
        }
    }
}
