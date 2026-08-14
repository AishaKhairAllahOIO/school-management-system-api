<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamSubject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run(): void
    {
       $exam1= Exam::updateOrCreate([
            'title' => 'مذاكرة رياضيات الشهرية',
            'academic_year_id'=>1,
            'semester_id'=>1,
            'grade_level_id' =>1,
            'type' =>'quiz'
        ]);
       $exam2=  Exam::updateOrCreate([
            'title' => 'امتحان الفصل الأول',
            'academic_year_id'=>1,
            'semester_id'=>1,
            'grade_level_id' =>1,
            'type' =>'exam'
        ]);


        ExamSubject::updateOrCreate([
        'exam_id' => $exam1->id,
        'grade_subject_id' => 1,
        'exam_date' => '2026-08-25',
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'syllabus' => 'بحث المعادلات من الدرجة الأولى'
        ]);
        ExamSubject::updateOrCreate([
        'exam_id' => $exam2->id,
        'grade_subject_id' => 1,
        'exam_date' => '2026-09-25',
        'start_time' => '08:00:00',
        'end_time' => '10:30:00',
        'syllabus' => 'كامل ابحاث الفصل الاول'
        ]);
    }
}
