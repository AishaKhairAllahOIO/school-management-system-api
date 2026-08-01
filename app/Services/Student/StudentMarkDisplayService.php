<?php

namespace App\Services\Student;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\StudentMark;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StudentMarkDisplayService
{
    protected array $targetTypes = ['quiz1', 'quiz2', 'exam'];

    private function getBaseMarkQueryForUser(User $user, ?int $studentId = null)
    {
        $enrollmentIds = [];

        if ($user->hasRole('student') && $user->student) {
            $enrollmentIds = $user->student->enrollments()->pluck('id');
        } elseif ($user->hasRole('guardian') && $user->guardian) {
            $studentsQuery = $user->guardian->students();

            if ($studentId) {
                $isMyChild = $user->guardian->students()->where('students.id', $studentId)->exists();
                if (!$isMyChild) {
                    throw new AccessDeniedHttpException('Access denied, this is not your child.', null, 403);
                }
                $studentsQuery->where('students.id', $studentId);
            }

            $studentIds = $studentsQuery->pluck('students.id');
            $enrollmentIds = Enrollment::whereIn('student_id', $studentIds)->pluck('id');
        }

        return StudentMark::whereIn('enrollment_id', $enrollmentIds)
            ->whereHas('assessmentComponent', function ($q) {
                $q->whereIn('type', $this->targetTypes);
            });
    }


    public function getMarks(User $user, ?int $studentId = null)
    {
        $marks = $this->getBaseMarkQueryForUser($user, $studentId)
            ->with([
                'assessmentComponent:id,name,type,max_mark,grade_subject_id',
                'assessmentComponent.gradeSubject.subject:id,subject_name',
                'teacher:id,first_name,last_name',
                'readers' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        return $marks->map(function ($mark) {
            $component = $mark->assessmentComponent;
            $subject = $component->gradeSubject->subject ?? null;
            $teacher = $mark->teacher;

            return [
                'id' => $mark->id,
                'subject_name' => $subject ? $subject->subject_name : 'Unknown Subject',
                'assessment_name' => $component->name,
                'assessment_type' => $component->type,
                'mark' => (float) $mark->mark,
                'max_mark' => (float) $component->max_mark,
                'teacher_name' => $teacher ? trim("{$teacher->first_name} {$teacher->last_name}") : 'Unknown',
                'is_read' => $mark->readers->isNotEmpty(),
                'date' => $mark->updated_at->format('Y-m-d H:i'),
            ];
        });
    }


    public function unreadCount(User $user, ?int $studentId = null): array
    {
        $count = $this->getBaseMarkQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        return [
            'unread_marks_count' => $count
        ];
    }


    public function markAllAsRead(User $user, ?int $studentId = null): array
    {
        $unreadMarkIds = $this->getBaseMarkQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        $syncData = [];
        $now = now();
        foreach ($unreadMarkIds as $id) {
            $syncData[$id] = ['read_at' => $now];
        }

        if (!empty($syncData)) {
            $user->readMarks()->syncWithoutDetaching($syncData);
        }

        return $this->unreadCount($user, $studentId);
    }
}
