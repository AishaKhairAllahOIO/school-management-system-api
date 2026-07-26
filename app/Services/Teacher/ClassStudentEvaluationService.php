<?php

namespace App\Services\Teacher;

use App\Jobs\SendPushNotification;
use App\Models\ClassStudentEvaluation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClassStudentEvaluationService
{
    public function getTeacherEvaluations(User $teacherUser, int $perPage = 15): LengthAwarePaginator
    {
        // 🚀 تم إعفاء السيرفر من استعلام المعلم هنا
        return ClassStudentEvaluation::where('teacher_id', $teacherUser->staff->id)
            ->with(['gradeSubject.subject:id,subject_name', 'enrollment.student.user:id,first_name,last_name,avatar', 'enrollment.classRoom:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    public function createEvaluation(User $teacherUser, array $data): ClassStudentEvaluation
    {
        return DB::transaction(function () use ($teacherUser, $data) {
            $evaluation = ClassStudentEvaluation::create([
                'teacher_id'       => $teacherUser->staff->id,
                'enrollment_id'    => $data['enrollment_id'],
                'grade_subject_id' => $data['grade_subject_id'],
                'rating'           => $data['rating'],
                'notes'            => $data['notes'] ?? null,
            ]);

            $evaluation->load(['gradeSubject.subject', 'enrollment.student.user', 'enrollment.student.guardian.user', 'enrollment.classRoom']);

            $this->dispatchEvaluationNotification($evaluation, false);

            return $evaluation;
        });
    }

    public function updateEvaluation(ClassStudentEvaluation $evaluation, array $data): ClassStudentEvaluation
    {
        return DB::transaction(function () use ($evaluation, $data) {
            $evaluation->update([
                'grade_subject_id' => $data['grade_subject_id'] ?? $evaluation->grade_subject_id,
                'enrollment_id'    => $data['enrollment_id'] ?? $evaluation->enrollment_id,
                'rating'           => $data['rating'] ?? $evaluation->rating,
                'notes'            => array_key_exists('notes', $data) ? $data['notes'] : $evaluation->notes,
            ]);

            $evaluation->load(['gradeSubject.subject', 'enrollment.student.user', 'enrollment.student.guardian.user', 'enrollment.classRoom']);

            $this->dispatchEvaluationNotification($evaluation, true);

            return $evaluation->fresh(['gradeSubject.subject', 'enrollment.student.user', 'enrollment.classRoom']);
        });
    }

    public function deleteEvaluation(ClassStudentEvaluation $evaluation): void
    {
        DB::transaction(function () use ($evaluation) {
            $evaluation->delete();
        });
    }

    private function getBaseQueryForUser(User $user, ?int $specificStudentId = null)
    {
        if ($user->hasRole('student') && $user->student) {
            return ClassStudentEvaluation::whereHas('enrollment', function ($q) use ($user) {
                $q->where('student_id', $user->student->id);
            });
        }

        if ($user->hasRole('guardian') && $user->guardian) {
            $studentsQuery = $user->guardian->students();

            if ($specificStudentId) {
                $isMyChild = $user->guardian->students()->where('students.id', $specificStudentId)->exists();
                
                if (!$isMyChild) {
                    throw new AccessDeniedHttpException('هذا الطالب لا يتبع لرعايتك، غير مصرح لك بالوصول لتقييماته.');
                }

                $studentsQuery->where('students.id', $specificStudentId);
            }

            $studentIds = $studentsQuery->pluck('students.id');

            return ClassStudentEvaluation::whereHas('enrollment', function ($q) use ($studentIds) {
                $q->whereIn('student_id', $studentIds);
            });
        }

        return ClassStudentEvaluation::query()->where('id', '<', 0);
    }

    public function unreadCount(User $user, ?int $studentId = null): int
    {
        return $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }

    public function markAllAsRead(User $user, ?int $studentId = null): void
    {
        $evaluationIds = $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        $syncData = [];
        $now = now();
        foreach ($evaluationIds as $id) {
            $syncData[$id] = ['read_at' => $now];
        }

        if (!empty($syncData)) {
            $user->readEvaluations()->syncWithoutDetaching($syncData);
        }
    }

    public function getStudentEvaluations(User $studentUser, int $perPage = 15): LengthAwarePaginator
    {
        if (!$studentUser->student) {
            throw new NotFoundHttpException('ملف الطالب غير موجود.');
        }

        return $this->getBaseQueryForUser($studentUser)
            ->withExists(['readers as is_read' => fn($q) => $q->where('user_id', $studentUser->id)])
            // 🚀 تم حذف تحميل المعلم من استعلام الطالب
            ->with(['gradeSubject.subject:id,subject_name', 'enrollment.student.user:id,first_name,last_name,avatar', 'enrollment.classRoom:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    public function getGuardianChildEvaluations(User $guardianUser, int $studentId, int $perPage = 15): LengthAwarePaginator
    {
        $child = Student::where('id', $studentId)
            ->where('guardian_id', $guardianUser->guardian?->id)
            ->first();

        if (!$child) {
            throw new AccessDeniedHttpException('غير مصرح لك بالوصول إلى تقييمات هذا الطالب لأنه غير مسجل تحت رعايتك.');
        }

        return $this->getBaseQueryForUser($guardianUser, $studentId)
            ->withExists(['readers as is_read' => fn($q) => $q->where('user_id', $guardianUser->id)])
            // 🚀 تم حذف تحميل المعلم من استعلام ولي الأمر أيضاً
            ->with(['gradeSubject.subject:id,subject_name', 'enrollment.student.user:id,first_name,last_name,avatar', 'enrollment.classRoom:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    private function dispatchEvaluationNotification(ClassStudentEvaluation $evaluation, bool $isUpdate = false): void
    {
        $student = $evaluation->enrollment?->student;
        if (!$student) return;

        $userIds = collect();
        if ($student->user_id) $userIds->push($student->user_id);
        if ($student->guardian?->user_id) $userIds->push($student->guardian->user_id);

        $uniqueTargetIds = $userIds->unique()->values()->toArray();

        if (!empty($uniqueTargetIds)) {
            $subjectName  = $evaluation->gradeSubject?->subject?->subject_name ?? 'مادة دراسية';
            $ratingArabic = $evaluation->getRatingArabicName();

            $title = $isUpdate
                ? "تحديث على تقييم الأداء: {$subjectName}"
                : "تقييم أداء جديد: {$subjectName}";

            $body  = $isUpdate
                ? "قام الأستاذ بتعديل تقييم الطالب إلى ({$ratingArabic})" . ($evaluation->notes ? " - ملاحظة: {$evaluation->notes}" : "")
                : "حصل الطالب على تقييم ({$ratingArabic}) من أستاذ المادة" . ($evaluation->notes ? " - ملاحظة: {$evaluation->notes}" : "");

            SendPushNotification::dispatch(
                $uniqueTargetIds,
                $title,
                $body,
                [
                    'type'          => $isUpdate ? 'update_evaluation' : 'new_evaluation',
                    'evaluation_id' => $evaluation->id,
                    'rating'        => $evaluation->rating,
                ]
            )->afterCommit();
        }
    }
}