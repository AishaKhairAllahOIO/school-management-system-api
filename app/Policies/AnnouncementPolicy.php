<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Traits\StaffAuthorizationTrait;

class AnnouncementPolicy
{
    use StaffAuthorizationTrait;

    public function create(User $user, string $targetType, ?int $gradeLevelId = null, ?array $classRoomIds = null): bool
    {
        if ($user->hasRole('teacher')) {
            return false;
        }

        if ($targetType === Announcement::AUDIENCE_STAFF || $targetType === Announcement::AUDIENCE_BOTH) {
            return $user->hasRole('super_admin');
        }

        if ($targetType === Announcement::AUDIENCE_STUDENT) {
            return $this->checkClassroomAccess($user, $gradeLevelId, $classRoomIds);
        }

        return false;
    }

    public function update(User $user, Announcement $announcement, string $targetType, ?int $gradeLevelId = null, ?array $classRoomIds = null): bool
    {
        if ($user->hasRole('teacher')) return false;

        if ($user->hasRole('super_admin')) return true;

        if ($targetType === Announcement::AUDIENCE_STAFF || $targetType === Announcement::AUDIENCE_BOTH) {
            return false;
        }

        if ($user->hasRole('advisor') || $user->hasRole('adviser')) {
            if ($announcement->audience !== Announcement::AUDIENCE_STUDENT) {
                return false;
            }

            $hasAccessToOld = $this->checkClassroomAccess($user, $announcement->grade_level_id, null);
            $hasAccessToNew = $this->checkClassroomAccess($user, $gradeLevelId, $classRoomIds);

            return $hasAccessToOld && $hasAccessToNew;
        }

        return false;
    }

    public function delete(User $user, $announcement): bool
    {
        if ($user->hasRole('teacher')) return false;

        if ($user->hasRole('super_admin')) return true;

        if ($user->hasRole('adviser') && Announcement::AUDIENCE_STUDENT) {
            return $this->checkClassroomAccess($user, $announcement->grade_level_id, null);
        }

        return false;
    }
}
