<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\StaffAuthorizationTrait;

class MarkPolicy
{
    use StaffAuthorizationTrait;

    public function viewGradebook(User $user, int $gradeSubjectId, int $classRoomId): bool
    {
        return $this->checkTeacherMarkAccess($user, $gradeSubjectId, $classRoomId);
    }

    public function updateMarks(User $user, int $gradeSubjectId, int $classRoomId): bool
    {
        return $this->checkTeacherMarkAccess($user, $gradeSubjectId, $classRoomId);
    }
}
