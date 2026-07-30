<?php

namespace App\Services\Staff;

use App\Models\TeacherWorkload;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\GradeSubject;

class TeacherWorkloadService
{
    // ==========================================
    // 1. إدارة النصاب (Teacher Workload)
    // ==========================================

public function createWorkload(array $data)
    {
return DB::transaction(function () use ($data) {
            $exists = TeacherWorkload::where('teacher_id', $data['teacher_id'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->exists();

            if ($exists) {
                http_response_code(422);
                throw new Exception('يوجد بالفعل نصاب محدد لهذا المعلم في هذه السنة الدراسية.');
            }

            $data['assigned_monthly_periods'] = 0;

            return TeacherWorkload::create($data)->fresh();
        });
    }

    public function getTeacherWorkloads(int $teacherId)
    {
        return TeacherWorkload::where('teacher_id', $teacherId)
            ->with(['academicYear'])
            ->get();
    }

 public function updateWorkload(int $id, array $data)
    {
        $workload=TeacherWorkload::find($id);
        $workload->fill($data);

        if ($workload->isDirty(['teacher_id', 'academic_year_id'])) {
            if ($workload->assigned_monthly_periods > 0) {
                throw new Exception("لا يمكن تغيير المعلم أو السنة الدراسية لهذا النصاب لوجود تكليفات فعلية مرتبطة به. يرجى تعديل التكليفات أولاً.");
            }
        }


        $workload->save();
        
        return $workload->fresh();
    }

public function deleteWorkload(int $id)
    {
        $workload=TeacherWorkload::find($id);
        $workload->delete();
    }


    // ==========================================
    // 2. إدارة التكليف (Teacher Assignment)
    // ==========================================

    public function assignTeacher(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            $workload = TeacherWorkload::where([
                'academic_year_id' => $data['academic_year_id'],
                'teacher_id'       => $data['teacher_id'],
            ])->first();

            // if (!$workload) {
            //     throw new Exception("لا يمكن إتمام التكليف: لم يتم تحديد النصاب الأساسي لهذا المعلم في هذه السنة الدراسية بعد.");
            // }
         $conflictingClassrooms = TeacherAssignment::where('semester_id', $data['semester_id'])
                ->where('grade_subject_id', $data['grade_subject_id'])
                ->whereIn('class_room_id', $data['class_room_ids'])
                ->pluck('class_room_id')
                ->toArray();

            if (!empty($conflictingClassrooms)) {
                $ids = implode(', ', $conflictingClassrooms);
                throw new Exception("عذراً، الشُعب ذات المعرفات ({$ids}) تمتلك بالفعل معلماً لهذه المادة في هذا الفصل. يرجى إزالة التحديد عنها وإعادة المحاولة.", 422);
            }
            $assignmentsToInsert = [];
            $now = now();

            foreach ($data['class_room_ids'] as $classroomId) {
                $assignmentsToInsert[] = [
                    'academic_year_id' => $data['academic_year_id'],
                    'semester_id' => $data['semester_id'],
                    'teacher_id'       => $data['teacher_id'],
                    'grade_subject_id' => $data['grade_subject_id'],
                    'class_room_id'     => $classroomId, 
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            TeacherAssignment::insert($assignmentsToInsert);

            // جلب الحصص من المادة الفعيلة
            $gradeSubject = GradeSubject::find($data['grade_subject_id']);
            $periodsPerClass = $gradeSubject ? $gradeSubject->weekly_periods : 0; 
            
            $classroomsCount = count($data['class_room_ids']);
            $totalAssignedPeriods = $classroomsCount * $periodsPerClass;

            $workload->increment('assigned_monthly_periods', $totalAssignedPeriods);
            $workload->fresh();
            return TeacherAssignment::where('teacher_id', $data['teacher_id'])
                                    ->where('semester_id', $data['semester_id'])
                                    ->get();
        });
    }

    public function getTeacherAssignments(int $teacherId)
    {
        return TeacherAssignment::where('teacher_id', $teacherId)
            ->with(['semester']) // سيتم إضافة gradeSubject و classroom لاحقاً
            ->get();
    }

    // 🔥 دالة تعديل التكليف
       public function updateAssignment(TeacherAssignment $assignment, array $data)
    {
        return DB::transaction(function () use ($assignment, $data) {
            
            // 1. تعبئة الموديل بالبيانات الجديدة (في الذاكرة فقط، لم تحفظ بعد)
            $assignment->fill($data);

            // 2. هل تغير أي شيء يخص النصاب؟ (المعلم، السنة، أو المادة)
            // إذا غيروا الشعبة فقط (classroom_id)، لن يدخل الكود هنا وسيوفر موارد السيرفر!
            if ($assignment->isDirty(['teacher_id', 'academic_year_id', 'grade_subject_id'])) {
                
                // --- أ) الخصم من الأستاذ القديم (نستخدم getOriginal لجلب القيمة قبل التعديل) ---
                $oldWorkload = TeacherWorkload::where([
                    'academic_year_id' => $assignment->getOriginal('academic_year_id'),
                    'teacher_id'       => $assignment->getOriginal('teacher_id'),
                ])->first();

                $oldSubject = GradeSubject::find($assignment->getOriginal('grade_subject_id'));
                $oldPeriods = $oldSubject ? $oldSubject->weekly_periods : 0;

                if ($oldWorkload && $oldPeriods > 0) {
                    $oldWorkload->decrement('assigned_monthly_periods', $oldPeriods);
                }

                // --- ب) الإضافة للأستاذ الجديد (أو نفس الأستاذ بمادة جديدة) ---
                $newWorkload = TeacherWorkload::where([
                    'academic_year_id' => $assignment->academic_year_id, // القيمة الجديدة
                    'teacher_id'       => $assignment->teacher_id,       // القيمة الجديدة
                ])->first();

                if (!$newWorkload) {
                    throw new Exception("لا يمكن إتمام التعديل: المعلم أو السنة المحددة ليس لها نصاب مسجل.");
                }

                $newSubject = GradeSubject::find($assignment->grade_subject_id); // القيمة الجديدة
                $newPeriods = $newSubject ? $newSubject->weekly_periods : 0;

                if ($newPeriods > 0) {
                    $newWorkload->increment('assigned_monthly_periods', $newPeriods);
                }
            }

            // 3. حفظ التعديل النهائي في قاعدة البيانات
            $assignment->save();

            return $assignment->fresh();
        });
    }

    public function deleteAssignment(TeacherAssignment $assignment)
    {
        return DB::transaction(function () use ($assignment) {
            
            $workload = TeacherWorkload::where([
                'academic_year_id' => $assignment->academic_year_id,
                'teacher_id'       => $assignment->teacher_id,
            ])->first();

            if ($workload) {
                $gradeSubject = GradeSubject::find($assignment->grade_subject_id);
                $periodsToSubtract = $gradeSubject ? $gradeSubject->weekly_periods : 0;
                
                if ($periodsToSubtract > 0) {
                    $workload->decrement('assigned_monthly_periods', $periodsToSubtract);
                }
            }

            $assignment->delete();
        });
    }
    //  public function getSubjectWorkloadStatus(int $gradeSubjectId): array
    // {
    //     // 1. جلب المادة لمعرفة تفاصيلها (عدد حصصها الأسبوعية، ولاي صف تتبع)
    //     $gradeSubject = GradeSubject::findOrFail($gradeSubjectId);

    //     // 2. كم شعبة موجودة في هذا الصف (Grade Level) في المدرسة؟
    //     $totalClassrooms = \App\Models\ClassRoom::where('grade_level_id', $gradeSubject->grade_level_id)->count();

    //     // 3. كم شعبة تم تغطيتها (أي تم تكليف أستاذ لها في هذه المادة)؟
    //     $assignedClassrooms = TeacherAssignment::where('grade_subject_id', $gradeSubject->id)->count();

    //     // 4. الشُعب المتبقية التي ليس لها أستاذ حتى الآن
    //     $unassignedClassrooms = max(0, $totalClassrooms - $assignedClassrooms);

    //     // 5. إرجاع التقرير الإداري الشامل
    //     return [
    //         'grade_subject_id'         => $gradeSubject->id,
    //         'weekly_periods_per_class' => $gradeSubject->weekly_periods, // حصص المادة للشعبة الواحدة
            
    //         'classrooms_stats' => [
    //             'total'      => $totalClassrooms,
    //             'assigned'   => $assignedClassrooms,
    //             'unassigned' => $unassignedClassrooms,
    //         ],
            
    //         'periods_stats' => [
    //             'total_required' => $totalClassrooms * $gradeSubject->weekly_periods,       // النصاب الكلي المطلوب للمدرسة
    //             'total_assigned' => $assignedClassrooms * $gradeSubject->weekly_periods,    // ما تم توزيعه على الأساتذة
    //             'remaining'      => $unassignedClassrooms * $gradeSubject->weekly_periods,  // 🔥 هذا هو الرقم الذي تريدينه! (العجز المتبقي)
    //         ]
    //     ];
    // }
}