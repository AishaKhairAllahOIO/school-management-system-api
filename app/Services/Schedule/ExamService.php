<?php

namespace App\Services\Schedule;

use App\Jobs\SendPushNotification;
use App\Models\Exam;
use App\Models\GradeSubject;
use App\Models\AcademicSetting;
use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\Staff;
use App\Services\User\AlertService;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ExamService
{

    public function __construct(
        private AlertService $alertService
    ) {
    }

    public function getSetupDataForGrade(int $gradeLevelId): array
    {
        $gradeSubjects = GradeSubject::with(['subject', 'teacherAssignments.staff.user'])
            ->where('grade_level_id', $gradeLevelId)
            ->get();

        $setupData = [];

        foreach ($gradeSubjects as $gradeSubject) {
            $teachers = $gradeSubject->teacherAssignments->map(function ($assignment) {
                return [
                    'teacher_id' => $assignment->teacher_id,
                    'teacher_name' => $assignment->staff->user->first_name . ' ' . $assignment->staff->user->last_name,
                ];
            })->unique('teacher_id')->values()->toArray();

            $setupData[] = [
                'grade_subject_id' => $gradeSubject->id,
                'subject_name' => $gradeSubject->subject->subject_name ?? 'Unknown',
                'auto_teachers' => $teachers,
            ];
        }

        return $setupData;
    }


    public function createExamSchedule(array $data): Exam
    {
        $exam = DB::transaction(function () use ($data) {
            $setting = AcademicSetting::firstOrFail();

            $exam = Exam::create([
                'title' => $data['title'],
                'type' => $data['type'],
                'grade_level_id' => $data['grade_level_id'],
                'academic_year_id' => $setting->current_academic_year_id,
                'semester_id' => $setting->current_semester_id,
            ]);

            foreach ($data['subjects'] as $subjectData) {
                $examSubject = $exam->subjects()->create([
                    'grade_subject_id' => $subjectData['grade_subject_id'],
                    'exam_date' => $subjectData['exam_date'],
                    'start_time' => $subjectData['start_time'],
                    'end_time' => $subjectData['end_time'],
                    'syllabus' => $subjectData['syllabus'] ?? null,
                ]);

                if (!empty($subjectData['teacher_ids'])) {
                    $examSubject->teachers()->attach($subjectData['teacher_ids']);
                }
            }

            return $exam;
        });

        $this->notifyUsersOfNewExam($exam);

        return $exam->load('subjects.teachers.user', 'subjects.gradeSubject.subject');
    }

    public function updateExamSchedule(int $examId, array $data): Exam
    {
        $exam = DB::transaction(function () use ($examId, $data) {
            $exam = Exam::findOrFail($examId);

            // 1. تحديث الحقول الأساسية إن وُجدت (مثلاً تعديل العنوان فقط)
            $basicFields = collect($data)->only(['title', 'type', 'grade_level_id'])->toArray();
            if (!empty($basicFields)) {
                $exam->update($basicFields);
            }

            // 2. تحديث المواد والأساتذة (فقط إذا قام الفرونت إند بإرسال مصفوفة subjects)
            if (array_key_exists('subjects', $data)) {

                // تنظيف القديم
                foreach ($exam->subjects as $subject) {
                    $subject->teachers()->detach();
                    $subject->delete();
                }

                // حفظ الجديد
                foreach ($data['subjects'] as $subjectData) {
                    $examSubject = $exam->subjects()->create([
                        'grade_subject_id' => $subjectData['grade_subject_id'],
                        'exam_date' => $subjectData['exam_date'],
                        'start_time' => $subjectData['start_time'],
                        'end_time' => $subjectData['end_time'],
                        'syllabus' => $subjectData['syllabus'] ?? null,
                    ]);

                    if (!empty($subjectData['teacher_ids'])) {
                        $examSubject->teachers()->attach($subjectData['teacher_ids']);
                    }
                }
            }

            // 3. مسح إشعارات القراءة ليعرف الطلاب أن الجدول قد تم تعديله
            DB::table('exam_reads')->where('exam_id', $exam->id)->delete();

            return $exam;
        });

        // 4. إرسال التنبيهات بالتحديث
        $this->notifyUsersOfExamUpdate($exam);

        return $exam->load('subjects.teachers.user', 'subjects.gradeSubject.subject');
    }

    private function notifyUsersOfExamUpdate(Exam $exam): void
    {
        try {
            $examTypeAr = $exam->type === 'quiz' ? 'مذاكرة' : 'امتحان';

            $studentTitle = "تعديل في برنامج ال{$examTypeAr} 🔄";
            $studentBody = "تم تحديث تفاصيل ومواعيد: {$exam->title}. يرجى الاطلاع على التعديلات الجديدة.";

            $staffTitle = "تعديل في برنامج ال{$examTypeAr} 🔄";
            $staffBody = "تم إجراء تعديلات على البرنامج: {$exam->title} الذي أنت مكلف به. يرجى المراجعة.";

            // سنستخدم نفس منطق الجلب السابق
            $alertsToInsert = [];
            $now = now();

            // للطلاب
            $enrollments = Enrollment::whereHas('classRoom', function ($q) use ($exam) {
                $q->where('grade_level_id', $exam->grade_level_id);
            })->whereHas('academicYear', fn($q) => $q->where('is_current', true))->with('student.user')->get();

            $studentUserIds = [];
            foreach ($enrollments as $enrollment) {
                $alertsToInsert[] = [
                    'title' => $studentTitle,
                    'description' => $studentBody,
                    'audience' => 'student',
                    'type' => 'exam_schedule_update',
                    'notifiable_type' => Enrollment::class,
                    'notifiable_id' => $enrollment->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($enrollment->student && $enrollment->student->user)
                    $studentUserIds[] = $enrollment->student->user->id;
            }

            // للموظفين
            $exam->loadMissing('subjects.teachers.user');
            $staffUserIds = [];
            $notifiedStaffIds = [];

            foreach ($exam->subjects as $subject) {
                foreach ($subject->teachers as $staff) {
                    if (!in_array($staff->id, $notifiedStaffIds)) {
                        $alertsToInsert[] = [
                            'title' => $staffTitle,
                            'description' => $staffBody,
                            'audience' => 'staff',
                            'type' => 'exam_schedule_update',
                            'notifiable_type' => Staff::class,
                            'notifiable_id' => $staff->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $notifiedStaffIds[] = $staff->id;
                        if ($staff->user)
                            $staffUserIds[] = $staff->user->id;
                    }
                }
            }

            if (!empty($alertsToInsert))
                Alert::insert($alertsToInsert);

            if (!empty($studentUserIds)) {
                SendPushNotification::dispatch(array_unique($studentUserIds), $studentTitle, $studentBody, [
                    'type' => 'update_exam_schedule',
                    'exam_id' => (string) $exam->id,
                    'target' => 'student'
                ]);
            }

            if (!empty($staffUserIds)) {
                SendPushNotification::dispatch(array_unique($staffUserIds), $staffTitle, $staffBody, [
                    'type' => 'update_exam_schedule',
                    'exam_id' => (string) $exam->id,
                    'target' => 'staff'
                ]);
            }
        } catch (Exception $e) {
            Log::error('Exam Update Notification Error', ['error' => $e->getMessage()]);
        }
    }

    private function notifyUsersOfNewExam(Exam $exam): void
    {
        try {
            $examTypeAr = $exam->type === 'quiz' ? 'مذاكرة' : 'امتحان';

            $studentTitle = "برنامج {$examTypeAr} جديد 📅";
            $studentBody = "تم إضافة تفاصيل لبرنامج: {$exam->title}. يرجى الاطلاع على الموعد والمقرر.";

            $staffTitle = "تكليف ببرنامج {$examTypeAr} 📅";
            $staffBody = "تم إدراجك كمعلم ضمن برنامج: {$exam->title}. يرجى مراجعة التفاصيل.";

            $alertsToInsert = [];
            $now = now();


            $enrollments = Enrollment::whereHas('classRoom', function ($q) use ($exam) {
                $q->where('grade_level_id', $exam->grade_level_id);
            })
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->with('student.user')
                ->get();

            $studentUserIds = [];

            foreach ($enrollments as $enrollment) {
                $alertsToInsert[] = [
                    'title' => $studentTitle,
                    'description' => $studentBody,
                    'audience' => 'student',
                    'type' => 'exam_schedule',
                    'notifiable_type' => Enrollment::class,
                    'notifiable_id' => $enrollment->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($enrollment->student && $enrollment->student->user) {
                    $studentUserIds[] = $enrollment->student->user->id;
                }
            }
            $exam->loadMissing('subjects.teachers.user');

            $staffUserIds = [];
            $notifiedStaffIds = [];

            foreach ($exam->subjects as $subject) {
                foreach ($subject->teachers as $staff) {
                    if (!in_array($staff->id, $notifiedStaffIds)) {
                        $alertsToInsert[] = [
                            'title' => $staffTitle,
                            'description' => $staffBody,
                            'audience' => 'staff',
                            'type' => 'exam_schedule',
                            'notifiable_type' => Staff::class,
                            'notifiable_id' => $staff->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $notifiedStaffIds[] = $staff->id;

                        if ($staff->user) {
                            $staffUserIds[] = $staff->user->id;
                        }
                    }
                }
            }


            if (!empty($alertsToInsert)) {
                Alert::insert($alertsToInsert);
            }

            if (!empty($studentUserIds)) {
                SendPushNotification::dispatch(
                    array_unique($studentUserIds),
                    $studentTitle,
                    $studentBody,
                    [
                        'type' => 'new_exam_schedule',
                        'exam_id' => (string) $exam->id,
                        'grade_level_id' => (string) $exam->grade_level_id,
                        'target' => 'student'
                    ]
                );
            }

            if (!empty($staffUserIds)) {
                SendPushNotification::dispatch(
                    array_unique($staffUserIds),
                    $staffTitle,
                    $staffBody,
                    [
                        'type' => 'new_exam_schedule',
                        'exam_id' => (string) $exam->id,
                        'grade_level_id' => (string) $exam->grade_level_id,
                        'target' => 'staff'
                    ]
                );
            }

        } catch (Exception $e) {
            Log::error('Exam Schedule Notification Error', ['error' => $e->getMessage()]);
        }
    }
    public function deleteExamSchedule(int $examId): void
    {
        $exam = Exam::findOrFail($examId);
        $exam->delete();
    }

    public function unreadCount($user, int $gradeLevelId, int $studentId): int
    {
        $setting = AcademicSetting::first();

        $totalExamsIds = Exam::where('grade_level_id', $gradeLevelId)
            ->where('academic_year_id', $setting->current_academic_year_id)
            ->where('semester_id', $setting->current_semester_id)
            ->pluck('id');

        if ($totalExamsIds->isEmpty()) {
            return 0;
        }

        $readCount = DB::table('exam_reads')
            ->where('user_id', $user->id)
            ->where('student_id', $studentId)
            ->whereIn('exam_id', $totalExamsIds)
            ->count();

        return max(0, $totalExamsIds->count() - $readCount);
    }

    public function markAllRead($user, int $gradeLevelId, int $studentId): void
    {
        $setting = AcademicSetting::first();

        $unreadExamsIds = Exam::where('grade_level_id', $gradeLevelId)
            ->where('academic_year_id', $setting->current_academic_year_id)
            ->where('semester_id', $setting->current_semester_id)
            ->whereNotIn('id', function ($query) use ($user, $studentId) {
                $query->select('exam_id')
                    ->from('exam_reads')
                    ->where('user_id', $user->id)
                    ->where('student_id', $studentId);
            })
            ->pluck('id');

        $inserts = $unreadExamsIds->map(function ($examId) use ($user, $studentId) {
            return [
                'exam_id' => $examId,
                'user_id' => $user->id,
                'student_id' => $studentId,
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (!empty($inserts)) {
            DB::table('exam_reads')->insert($inserts);
        }
    }


    public function getStudentExams(int $gradeLevelId, int $studentId, int $userId): array
    {
        $setting = AcademicSetting::firstOrFail();

        $exams = Exam::with(['subjects.gradeSubject.subject'])
            ->where('grade_level_id', $gradeLevelId)
            ->where('academic_year_id', $setting->current_academic_year_id)
            ->where('semester_id', $setting->current_semester_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $readExamIds = DB::table('exam_reads')
            ->where('student_id', $studentId)
            ->where('user_id', $userId)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->pluck('exam_id')
            ->toArray();

        $result = [];
        foreach ($exams as $exam) {
            $subjects = $exam->subjects->map(function ($examSubject) {
                return [
                    'subject_name' => $examSubject->gradeSubject->subject->subject_name ?? 'Unknown',
                    'exam_date' => $examSubject->exam_date,
                    'start_time' => $examSubject->start_time,
                    'end_time' => $examSubject->end_time,
                    'syllabus' => $examSubject->syllabus,
                ];
            })->sortBy('exam_date')->values()->toArray();

            $result[] = [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'type' => $exam->type,
                'subjects' => $subjects,
                'is_read' => in_array($exam->id, $readExamIds),
            ];
        }

        return $result;
    }


    public function getTeacherExams(int $teacherId): array
    {
        $setting = AcademicSetting::firstOrFail();

        $exams = Exam::whereHas('subjects.teachers', function ($query) use ($teacherId) {
            $query->where('staff.id', $teacherId);
        })
            ->with([
                'gradeLevel',
                'subjects' => function ($query) use ($teacherId) {
                    $query->whereHas('teachers', function ($q) use ($teacherId) {
                        $q->where('staff.id', $teacherId);
                    })->with('gradeSubject.subject');
                }
            ])
            ->where('academic_year_id', $setting->current_academic_year_id)
            ->where('semester_id', $setting->current_semester_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];
        foreach ($exams as $exam) {
            $subjects = $exam->subjects->map(function ($examSubject) {
                return [
                    'subject_name' => $examSubject->gradeSubject->subject->subject_name ?? 'Unknown',
                    'exam_date' => $examSubject->exam_date,
                    'start_time' => $examSubject->start_time,
                    'end_time' => $examSubject->end_time,
                    'syllabus' => $examSubject->syllabus,
                ];
            })->sortBy('exam_date')->values()->toArray();

            $result[] = [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'type' => $exam->type,
                'grade_name' => $exam->gradeLevel->name->value ?? 'Unknown Grade',
                'subjects' => $subjects
            ];
        }

        return $result;
    }

public function getAllExamsForAdmin(int $academicYearId, int $semesterId): array
{
    $exams = Exam::query()
        ->where('academic_year_id', $academicYearId)
        ->where('semester_id', $semesterId)
        ->with([
            'gradeLevel',
            'semester',

            'subjects' => function ($query) {
                $query
                    ->with([
                        'gradeSubject.subject',
                        'teachers.user',
                    ])
                    ->orderBy('exam_date')
                    ->orderBy('start_time');
            },
        ])
        ->orderBy('grade_level_id')
        ->orderBy('created_at', 'desc')
        ->get();


    return $exams->map(function ($exam) {

        $subjects = $exam->subjects->map(function ($examSubject) {

            $teachers = $examSubject->teachers
                ->map(function ($staff) {
                    return [
                        'staff_id' => $staff->id,
                        'teacher_id' => $staff->teacher_id ?? $staff->id,
                        'teacher_name' => trim(
                            "{$staff->user?->first_name} {$staff->user?->father_name} {$staff->user?->last_name}"
                        ),
                    ];
                })
                ->unique('staff_id')
                ->values()
                ->toArray();


            return [
                'exam_subject_id' => $examSubject->id,

                'grade_subject_id' => $examSubject->grade_subject_id,

                'subject_id' =>
                    $examSubject->gradeSubject?->subject_id,

                'subject_name' =>
                    $examSubject->gradeSubject?->subject?->subject_name
                    ?? 'Unknown',

                'exam_date' => $examSubject->exam_date,
                'start_time' => $examSubject->start_time,
                'end_time' => $examSubject->end_time,

                'syllabus' => $examSubject->syllabus,

                'teachers' => $teachers,
            ];

        })->values()->toArray();


        return [
            'exam_id' => $exam->id,

            'title' => $exam->title,

            'type' => $exam->type,

            'grade_level' => [
                'id' => $exam->grade_level_id,
                'name' => $exam->gradeLevel?->name,
            ],

            'semester' => [
                'id' => $exam->semester_id,
                'name' => $exam->semester?->semester_name,
            ],

            'academic_year_id' => $exam->academic_year_id,

            'subjects' => $subjects,
        ];

    })->values()->toArray();
}






}