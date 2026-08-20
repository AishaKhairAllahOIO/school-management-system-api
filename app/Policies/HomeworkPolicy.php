<?php

namespace App\Policies;

use App\Models\Homework;
use App\Models\User;
use App\Traits\StaffAuthorizationTrait;

class HomeworkPolicy
{
    use StaffAuthorizationTrait;


    public function create(User $user, int $gradeSubjectId, array $classRoomIds): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            return $this->checkTeacherHomeworkAccess($user, $gradeSubjectId, $classRoomIds);
        }

        return false;
    }
  public function update(User $user, Homework $homework): bool
{
    return $homework->teacher_id === $user->staff?->id
        || $user->hasRole('super_admin');
}
    public function delete(User $user, Homework $homework): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            return (int) $homework->teacher_id === (int) $user->staff->id;
        }

        return false;
    }
    public function view(User $user, Homework $homework): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            return (int) $homework->teacher_id === (int) $user->staff->id;
        }

        return false;
    }
}
