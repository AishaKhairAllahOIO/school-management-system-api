<?php

namespace App\Services\Staff;

use App\Models\TeacherWorkload;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\GradeSubject;

class TeacherWorkloadService
{

public function createWorkload(array $data)
    {
return DB::transaction(function () use ($data) {
            $exists = TeacherWorkload::where('teacher_id', $data['teacher_id'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->exists();

            if ($exists) {
                http_response_code(422);
                throw new Exception('يوجد بالفعل نصاب محدد لهذا المعلم في هذه السنة الدراسية.',422);
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
                throw new Exception("لا يمكن تغيير المعلم أو السنة الدراسية لهذا النصاب لوجود تكليفات فعلية مرتبطة به. يرجى تعديل التكليفات أولاً.",422);
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



    public function assignTeacher(array $data)
    {
        return DB::transaction(function () use ($data) {

            $workload = TeacherWorkload::where([
                'academic_year_id' => $data['academic_year_id'],
                'teacher_id'       => $data['teacher_id'],
            ])->first();


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
            ->with(['semester'])
            ->get();
    }

       public function updateAssignment(TeacherAssignment $assignment, array $data)
    {
        return DB::transaction(function () use ($assignment, $data) {

            $assignment->fill($data);

            if ($assignment->isDirty(['teacher_id', 'academic_year_id', 'grade_subject_id'])) {

                $oldWorkload = TeacherWorkload::where([
                    'academic_year_id' => $assignment->getOriginal('academic_year_id'),
                    'teacher_id'       => $assignment->getOriginal('teacher_id'),
                ])->first();

                $oldSubject = GradeSubject::find($assignment->getOriginal('grade_subject_id'));
                $oldPeriods = $oldSubject ? $oldSubject->weekly_periods : 0;

                if ($oldWorkload && $oldPeriods > 0) {
                    $oldWorkload->decrement('assigned_monthly_periods', $oldPeriods);
                }

                $newWorkload = TeacherWorkload::where([
                    'academic_year_id' => $assignment->academic_year_id,
                    'teacher_id'       => $assignment->teacher_id,
                ])->first();

                if (!$newWorkload) {
                    throw new Exception("لا يمكن إتمام التعديل: المعلم أو السنة المحددة ليس لها نصاب مسجل.",422);
                }

                $newSubject = GradeSubject::find($assignment->grade_subject_id);
                $newPeriods = $newSubject ? $newSubject->weekly_periods : 0;

                if ($newPeriods > 0) {
                    $newWorkload->increment('assigned_monthly_periods', $newPeriods);
                }
            }

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
