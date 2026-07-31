<?php

namespace App\Traits;

use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\GradeConfiguration;

trait StaffAuthorizationTrait
{
    public function checkClassroomAccess(User $user, ?int $gradeLevelId = null, ?array $classRoomIds = null): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($gradeLevelId === null) {
            return false;
        }

        if ($user->hasRole('adviser')) {
            $isAdvisorForGrade = GradeConfiguration::where('supervisor_id', $user->id)
                ->where('grade_level_id', $gradeLevelId)
                ->whereHas('academicYear', function ($query) {
                    $query->where('is_current', true);
                })->exists();

            return $isAdvisorForGrade;
        }

        if ($user->hasRole('teacher')) {
            if (empty($classRoomIds)) {
                return false;
            }

            $teacherClassRooms = $user->staff->teacherAssignments()->pluck('class_room_id')->toArray();
            $hasUnauthorizedRooms = !empty(array_diff($classRoomIds, $teacherClassRooms));

            if ($hasUnauthorizedRooms) {
                return false;
            }

            $validGradeForTheseRooms = ClassRoom::whereIn('id', $classRoomIds)
                ->where('grade_level_id', $gradeLevelId)
                ->count();

            if ($validGradeForTheseRooms !== count($classRoomIds)) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function checkTeacherHomeworkAccess(User $user, int $gradeSubjectId, array $classRoomIds): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('advisor') || $user->hasRole('adviser')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            if (empty($classRoomIds)) {
                return false;
            }

            $assignedRoomsForGradeSubject = $user->staff->teacherAssignments()
                ->where('grade_subject_id', $gradeSubjectId)
                ->pluck('class_room_id')
                ->toArray();

            $hasUnauthorizedRooms = !empty(array_diff($classRoomIds, $assignedRoomsForGradeSubject));

            if ($hasUnauthorizedRooms) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function checkTeacherEvaluationAccess(User $user, int $gradeSubjectId, int $enrollmentId): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('adviser')) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->staff) {
            $enrollment = Enrollment::find($enrollmentId);
            if (!$enrollment) {
                return false;
            }

            return $user->staff->teacherAssignments()
                ->where('grade_subject_id', $gradeSubjectId)
                ->where('class_room_id', $enrollment->class_room_id)
                ->whereHas('academicYear', fn($query) => $query->where('is_current', true))
                ->exists();
        }

        return false;
    }

    public function checkTeacherMarkAccess(User $user, int $gradeSubjectId, int $classRoomId): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('adviser')) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->staff) {
            return $user->staff->teacherAssignments()
                ->where('grade_subject_id', $gradeSubjectId)
                ->where('class_room_id', $classRoomId)
                ->whereHas('academicYear', fn($query) => $query->where('is_current', true))
                ->exists();
        }

        return false;
    }
}
