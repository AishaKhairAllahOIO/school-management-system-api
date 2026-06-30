<?php

namespace App\Services\User;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Jobs\SendAnnouncementNotification;

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


}
