<?php

namespace App\Services\User;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Jobs\SendAnnouncementNotification;
use App\Models\User;

class AnnouncementService
{



    public function create(array $data): Announcement
    {
        $announcement = Announcement::create($data);

        SendAnnouncementNotification::dispatch(
            $announcement->id,
            $announcement->audience,
            $announcement->title,
            $announcement->description ?? ''
        );

        return $announcement;
    }

    public function delete(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
    }

    public function forStaff(): LengthAwarePaginator
    {
        return Announcement::forAudience(Announcement::AUDIENCE_STAFF)
            ->latest()
            ->paginate(20);
    }

    public function forStudent(): LengthAwarePaginator
    {
        return Announcement::forAudience(Announcement::AUDIENCE_STUDENT)
            ->latest()
            ->paginate(20);
    }

    public function unreadCount(User $user): int
    {
        $allowedAudiences = $user->hasRole('student')
            ? [Announcement::AUDIENCE_STUDENT, Announcement::AUDIENCE_BOTH]
            : [Announcement::AUDIENCE_STAFF, Announcement::AUDIENCE_BOTH];

        return Announcement::whereIn('audience', $allowedAudiences)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }

    public function markAllAsRead(User $user)
    {
        $allowedAudiences = $user->hasRole('student')
            ? [Announcement::AUDIENCE_STUDENT, Announcement::AUDIENCE_BOTH]
            : [Announcement::AUDIENCE_STAFF, Announcement::AUDIENCE_BOTH];

        $announcementIds = Announcement::whereIn('audience', $allowedAudiences)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        $syncData = [];
        foreach ($announcementIds as $id) {
            $syncData[$id] = ['read_at' => now()];
        }

        if (!empty($syncData)) {
            $user->readAnnouncements()->syncWithoutDetaching($syncData);
        }
    }
}
