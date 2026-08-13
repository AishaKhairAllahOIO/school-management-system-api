<?php

namespace App\Services\Schedule;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradeSubject;
use App\Models\AcademicSetting;
use Illuminate\Support\Facades\DB;
use Exception;

class ExamService
{
    /**
     * جلب المواد الخاصة بصف معين مع أساتذتها (ليتم تعبئتها تلقائياً في الواجهة)
     */
    public function getSetupDataForGrade(int $gradeLevelId): array
    {
        // جلب مواد هذا الصف مع الأساتذة المرتبطين بها (من جدول التعيينات)
        // ملاحظة: افترضت وجود علاقة 'teachers' أو 'teacherAssignments' في مودل GradeSubject
        $gradeSubjects = GradeSubject::with(['subject', 'teacherAssignments.teacher.user'])
            ->where('grade_level_id', $gradeLevelId)
            ->get();

        $setupData = [];

        foreach ($gradeSubjects as $gradeSubject) {
            // استخراج الأساتذة الذين يدرسون هذه المادة (بدون تكرار)
            $teachers = $gradeSubject->teacherAssignments->map(function ($assignment) {
                return [
                    'teacher_id'   => $assignment->teacher_id,
                    'teacher_name' => $assignment->teacher->user->first_name . ' ' . $assignment->teacher->user->last_name,
                ];
            })->unique('teacher_id')->values()->toArray();

            $setupData[] = [
                'grade_subject_id' => $gradeSubject->id,
                'subject_name'     => $gradeSubject->subject->name ?? 'Unknown',
                'auto_teachers'    => $teachers, // هذه المصفوفة ستُعبأ تلقائياً في الواجهة
            ];
        }

        return $setupData;
    }

    /**
     * إنشاء برنامج امتحان جديد دفعة واحدة (Transaction)
     */
    public function createExamSchedule(array $data): Exam
    {
        return DB::transaction(function () use ($data) {
            $setting = AcademicSetting::firstOrFail();

            // 1. إنشاء الترويسة الأساسية للبرنامج
            $exam = Exam::create([
                'title'            => $data['title'], // مثلاً: مذاكرات الفصل الأول
                'type'             => $data['type'], // 'exam' or 'quiz'
                'grade_level_id'   => $data['grade_level_id'],
                'academic_year_id' => $setting->current_academic_year_id,
                'semester_id'      => $setting->current_semester_id,
            ]);

            // 2. إنشاء تفاصيل المواد وربط الأساتذة
            foreach ($data['subjects'] as $subjectData) {
                $examSubject = $exam->subjects()->create([
                    'grade_subject_id' => $subjectData['grade_subject_id'],
                    'exam_date'        => $subjectData['exam_date'],
                    'start_time'       => $subjectData['start_time'],
                    'end_time'         => $subjectData['end_time'],
                    'syllabus'         => $subjectData['syllabus'] ?? null, // المقرر
                ]);

                // ربط الأساتذة الذين تم تحديدهم (أو تعبئتهم تلقائياً) بهذه المادة
                if (!empty($subjectData['teacher_ids'])) {
                    $examSubject->teachers()->attach($subjectData['teacher_ids']);
                }
            }

            return $exam->load('subjects.teachers.user', 'subjects.gradeSubject.subject');
        });
    }

    // دوال التعديل والحذف...
    public function deleteExamSchedule(int $examId): void
    {
        $exam = Exam::findOrFail($examId);
        $exam->delete(); // تأكد من استخدام Cascading في الـ Migration لحذف المواد والأساتذة المرتبطين
    }
}