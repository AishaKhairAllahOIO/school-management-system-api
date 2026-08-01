<?php

namespace Database\Seeders;

use App\Models\PracticeQuiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PracticeQuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {


            $quiz1 = PracticeQuiz::create([
                'grade_subject_id' => 1,
                'teacher_id'       => 9,
                'title'            => 'اختبار تجريبي: أساسيات التقنية',
                'description'      => 'هذا الاختبار مخصص لقياس فهمك للمصطلحات التقنية الأساسية.',
                'is_active'        => true,
            ]);

            $q1 = Question::create([
                'practice_quiz_id' => $quiz1->id,
                'question_text'    => 'ما هو العقل المدبر للحاسوب؟',
                'mark'             => 5.0,
            ]);

            Option::insert([
                ['question_id' => $q1->id, 'option_text' => 'وحدة المعالجة المركزية (CPU)', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q1->id, 'option_text' => 'القرص الصلب (Hard Disk)', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q1->id, 'option_text' => 'الشاشة', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);

            $q2 = Question::create([
                'practice_quiz_id' => $quiz1->id,
                'question_text'    => 'أي من التالي يُستخدم لتخزين البيانات بشكل دائم؟',
                'mark'             => 5.0,
            ]);

            Option::insert([
                ['question_id' => $q2->id, 'option_text' => 'ذاكرة الوصول العشوائي (RAM)', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q2->id, 'option_text' => 'القرص الصلب (ROM/SSD)', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);



            $quiz2 = PracticeQuiz::create([
                'grade_subject_id' => 2,
                'teacher_id'       => 9,
                'title'            => 'اختبار سريع: المنطق البرمجي',
                'description'      => 'اختبر مهاراتك في التفكير المنطقي والشرطي.',
                'is_active'        => true,
            ]);

            $q3 = Question::create([
                'practice_quiz_id' => $quiz2->id,
                'question_text'    => 'ما هي نتيجة الشرط التالي: (5 > 3) AND (10 < 5) ؟',
                'mark'             => 10.0,
            ]);

            Option::insert([
                ['question_id' => $q3->id, 'option_text' => 'True', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q3->id, 'option_text' => 'False', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);

        });
    }
}
