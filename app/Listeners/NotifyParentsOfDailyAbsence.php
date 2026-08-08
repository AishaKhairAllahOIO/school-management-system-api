<?php

namespace App\Listeners;

use App\Events\BulkAttendanceSaved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Enrollment;
use App\Services\User\AlertService;
use App\Models\Alert;

class NotifyParentsOfDailyAbsence 
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function handle(BulkAttendanceSaved $event): void
    {
        $dailyAbsents = collect($event->attendances)->where('status', 'absent');

        if ($dailyAbsents->isEmpty()) return;

        $dailyAbsentsIds = $dailyAbsents->pluck('enrollment_id')->toArray();

        // 💡 استخراج تاريخ التفقد الحقيقي الذي أرسلتيه من البوستمان!
        $actualAttendanceDate = $dailyAbsents->first()['attendance_date'] ?? now()->toDateString();

        // تمرير التاريخ الحقيقي للسيرفس الخاص بكِ
        $this->alertService->createBatchStudentAlerts(
            $dailyAbsentsIds,
            Alert::TYPE_ABSENCE,
            ['date' => $actualAttendanceDate] // 👈 التعديل السحري هنا
        );
    }
}
