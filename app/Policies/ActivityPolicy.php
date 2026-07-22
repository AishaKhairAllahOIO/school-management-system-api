<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use App\Traits\StaffAuthorizationTrait;

class ActivityPolicy
{
    use StaffAuthorizationTrait;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'advisor', 'teacher']);
    }

    public function create(User $user, int $gradeLevelId, ?array $classRoomIds = null): bool
    {
        return $this->checkClassroomAccess($user, $gradeLevelId, $classRoomIds);
    }

    public function update(User $user, Activity $activity, ?int $newGradeLevelId = null, ?array $newClassRoomIds = null): bool
    {
        $gradeLevelId = $newGradeLevelId ?? $activity->grade_level_id;
        $classRoomIds = $newClassRoomIds !== null ? $newClassRoomIds : $activity->classRooms()->pluck('class_rooms.id')->toArray();

        return $this->checkClassroomAccess($user, $gradeLevelId, $classRoomIds);
    }

    public function delete(User $user, Activity $activity): bool
    {
        if ($user->hasRole('super_admin')) return true;

        if ($user->hasRole('advisor')) {
            return $this->checkClassroomAccess($user, $activity->grade_level_id, null);
        }

        return false;
    }
}
