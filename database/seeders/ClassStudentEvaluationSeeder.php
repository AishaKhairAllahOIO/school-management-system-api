<?php

namespace Database\Seeders;

use App\Models\ClassStudentEvaluation;
use Illuminate\Database\Seeder;

class ClassStudentEvaluationSeeder extends Seeder
{

    public function run(): void
    {
        $teacherId = 1;
        $enrollmentIds = [1, 3];

        $gradeSubjectId = 1;

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
