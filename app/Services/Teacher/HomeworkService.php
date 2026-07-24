<?php

namespace App\Services\Teacher;

use App\Jobs\SendPushNotification;
use App\Models\Enrollment; // 👈 1. لا تنس استدعاء موديل التسجيل الدراسي
use App\Models\Homework;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomeworkService
{
    public function createHomework(User $teacherUser, array $data): Homework
    {
        return DB::transaction(function () use ($teacherUser, $data) {

            $homework = Homework::create([
                'teacher_id' => $teacherUser->staff->id,
                'grade_subject_id' => $data['grade_subject_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'],
            ]);

            $homework->classRooms()->attach($data['class_room_ids']);

            $homework->load(['gradeSubject.subject', 'classRooms']);

            $this->dispatchNotificationToStudentsAndGuardians($homework, $data['class_room_ids']);

            return $homework;
        });
    }
    public function updateHomework(Homework $homework, array $data): Homework
    {
        return DB::transaction(function () use ($homework, $data) {
            $homework->update([
                'grade_subject_id' => $data['grade_subject_id'] ?? $homework->grade_subject_id,
                'title' => $data['title'] ?? $homework->title,
                'description' => $data['description'] ?? $homework->description,
                'due_date' => $data['due_date'] ?? $homework->due_date,
            ]);

            if (isset($data['class_room_ids'])) {
                $homework->classRooms()->sync($data['class_room_ids']);
                $classRoomIds = $data['class_room_ids'];
            } else {
                $classRoomIds = $homework->classRooms()->allRelatedIds()->toArray();
            }

            $homework->load(['gradeSubject.subject']);

            $this->dispatchNotificationToStudentsAndGuardians($homework, $classRoomIds, true);

            return $homework->fresh(['gradeSubject.subject', 'gradeSubject.gradeLevel', 'classRooms']);
        });
    }
    public function deleteHomework(Homework $homework): bool
    {
        return $homework->delete();
    }
    public function getTeacherHomeworks(User $teacherUser, int $perPage = 15): LengthAwarePaginator
    {
        return Homework::where('teacher_id', $teacherUser->staff->id)
            ->with(['gradeSubject.subject:id,subject_name', 'gradeSubject.gradeLevel:id,name', 'classRooms:id,name'])
            ->latest()
            ->paginate($perPage);
    }
    public function getStudentHomeworks(User $studentUser, int $perPage = 15): LengthAwarePaginator
    {
        $student = $studentUser->student;

        if (!$student) {
            throw new NotFoundHttpException('ملف الطالب غير موجود.');
        }

        $classRoomId = Enrollment::where('student_id', $student->id)
            ->latest()
            ->value('class_room_id');

        if (!$classRoomId) {
            throw new NotFoundHttpException('لا يوجد تسجيل دراسي أو شعبة مرتبطة بهذا الطالب حالياً.');
        }

        return Homework::whereHas('classRooms', fn($q) => $q->where('class_room_id', $classRoomId))
            ->whereHas('gradeSubject.academicYear', fn($ay) => $ay->where('is_current', true))
            ->with(['gradeSubject.subject:id,subject_name', 'staff.user:id,first_name,last_name'])
            ->latest()
            ->paginate($perPage);
    }
    public function getGuardianChildHomeworks(User $guardianUser, int $studentId, int $perPage = 15): LengthAwarePaginator
    {
        $child = Student::where('id', $studentId)
            ->where('guardian_id', $guardianUser->guardian?->id)
            ->first();

        if (!$child) {
            throw new AccessDeniedHttpException('غير مصرح لك بالوصول إلى بيانات هذا الطالب لأنه غير مسجل تحت رعايتك.');
        }

        $classRoomId = Enrollment::where('student_id', $child->id)
            ->latest()
            ->value('class_room_id');

        if (!$classRoomId) {
            throw new NotFoundHttpException('هذا الطالب غير موزع على شعبة دراسية بعد.');
        }

        return Homework::whereHas('classRooms', fn($q) => $q->where('class_room_id', $classRoomId))
            ->whereHas('gradeSubject.academicYear', fn($ay) => $ay->where('is_current', true))
            ->with(['gradeSubject.subject:id,subject_name', 'staff.user:id,first_name,last_name'])
            ->latest()
            ->paginate($perPage);
    }
    private function dispatchNotificationToStudentsAndGuardians(Homework $homework, array $classRoomIds, bool $isUpdate = false): void
    {
        $enrollments = Enrollment::whereIn('class_room_id', $classRoomIds)
            ->with(['student.user:id', 'student.guardian.user:id'])
            ->get();

        $userIds = collect();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student)
                continue;

            // إضافة معرف حساب الطالب
            if ($student->user_id) {
                $userIds->push($student->user_id);
            }
            // إضافة معرف حساب الأب / ولي الأمر
            if ($student->guardian && $student->guardian->user_id) {
                $userIds->push($student->guardian->user_id);
            }
        }

        // استبعاد التكرار
        $uniqueTargetIds = $userIds->unique()->values()->toArray();

        if (!empty($uniqueTargetIds)) {
            $subjectName = $homework->gradeSubject?->subject?->subject_name ?? 'مادة دراسية';

            // 🚀 صياغة العنوان والمحتوى بذكاء بناءً على نوع العملية (إنشاء أم تعديل)
            $title = $isUpdate
                ? "تحديث على وظيفة منزلية: {$subjectName}"
                : "وظيفة منزلية جديدة: {$subjectName}";

            $body = $isUpdate
                ? "قام الأستاذ بتعديل تفاصيل أو موعد تسليم الوظيفة: ({$homework->title})، الموعد الجديد للتسليم: {$homework->due_date}"
                : "قام الأستاذ بإضافة وظيفة جديدة: ({$homework->title})، مطلوب تسليمها بتاريخ: {$homework->due_date}";

            $type = $isUpdate ? 'update_homework' : 'new_homework';

            SendPushNotification::dispatch(
                $uniqueTargetIds,
                $title,
                $body,
                [
                    'type' => $type,
                    'homework_id' => $homework->id,
                    'grade_subject_id' => $homework->grade_subject_id,
                ]
            )->afterCommit();
        }
    }
}
