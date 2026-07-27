<?php

namespace App\Policies;

use App\Models\ClassStudentEvaluation;
use App\Models\User;
use App\Traits\StaffAuthorizationTrait;

class ClassStudentEvaluationPolicy
{
    use StaffAuthorizationTrait;

    public function view(User $user, ClassStudentEvaluation $evaluation): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('adviser')) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->staff) {
            return (int) $evaluation->teacher_id === (int) $user->staff->id;
        }

        return false;
    }
    public function create(User $user, int $gradeSubjectId, int $enrollmentId): bool
    {
        return $this->checkTeacherEvaluationAccess($user, $gradeSubjectId, $enrollmentId);
    }
    public function update(User $user, ClassStudentEvaluation $evaluation, int $gradeSubjectId, int $enrollmentId): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->staff) {
            $isOwner = (int) $evaluation->teacher_id === (int) $user->staff->id;

            return $isOwner && $this->checkTeacherEvaluationAccess($user, $gradeSubjectId, $enrollmentId);
        }

        return false;
    }
    public function delete(User $user, ClassStudentEvaluation $evaluation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->staff) {
            return (int) $evaluation->teacher_id === (int) $user->staff->id;
        }

        return false;
    }

}
