<?php

namespace App\Observers;

use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Staff;
use App\Services\User\AlertService;

class ClassRoomObserver
{
    public function __construct(
        private AlertService $alertService
    ) {}
   public function created(ClassRoom $classRoom): void
    {

        $scheduleExists = Schedule::exists();

        if ($scheduleExists) {
            $admins = Staff::whereHas('user', function ($query) {
                $query->role(['super_admin', 'secretary']);
            })->get();

            foreach ($admins as $admin) {
                $this->alertService->createSystemNotice(
                    $admin,
                    'Schedule Update Required ⚠️',
                    "A new classroom ({$classRoom->name}) has been added. Please regenerate the schedule to ensure the new class is included in the timetable."
                );
            }
        }
    }


    public function updated(ClassRoom $classRoom): void
    {
        //
    }


    public function deleted(ClassRoom $classRoom): void
    {
        //
    }


    public function restored(ClassRoom $classRoom): void
    {
        //
    }


    public function forceDeleted(ClassRoom $classRoom): void
    {
        //
    }
}
