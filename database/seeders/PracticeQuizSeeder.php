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


            $quiz1 = PracticeQuiz::updateOrCreate([
                'grade_subject_id' => 1,
                'teacher_id'       => 1,
                'title'            => ' الاساسيات الرياضية(هندسة)',
                'description'      => 'اختبر مهاراتك في الرياضيات والهندسة من خلال هذا الاختبار.',
                'is_active'        => true,
            ]);

            $q1 = Question::updateOrCreate([
                'practice_quiz_id' => $quiz1->id,
                'question_text'    => 'ما هو عدد الأضلاع في مثلث متساوي الأضلاع؟',
                'mark'             => 5.0,
            ]);

            Option::insert([
                ['question_id' => $q1->id, 'option_text' => '3', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q1->id, 'option_text' => '4', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q1->id, 'option_text' => '5', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);

            $q2 = Question::updateOrCreate([
                'practice_quiz_id' => $quiz1->id,
                'question_text'    => 'من خواص متوازي الأضلاع أن جميع أضلاعه متساوية في الطول؟',
                'mark'             => 5.0,
            ]);

            Option::insert([
                ['question_id' => $q2->id, 'option_text' => 'True', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q2->id, 'option_text' => 'False', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);



            $quiz2 = PracticeQuiz::updateOrCreate([
                'grade_subject_id' => 8,
                'teacher_id'       => 1,
                'title'            => 'اختبار سريع: المنطق الفيزيائي',
                'description'      => 'اختبر مهاراتك في التفكير الفيزيائي والحركي.',
                'is_active'        => true,
            ]);

            $q3 = Question::updateOrCreate([
                'practice_quiz_id' => $quiz2->id,
                'question_text'    => 'هل يمكن أن يكون الضوء جسيمًا وموجة في نفس الوقت؟',
                'mark'             => 10.0,
            ]);

            Option::insert([
                ['question_id' => $q3->id, 'option_text' => 'True', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()],
                ['question_id' => $q3->id, 'option_text' => 'False', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);

        });
    }
}
