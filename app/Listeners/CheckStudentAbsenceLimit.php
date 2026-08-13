<?php

namespace App\Listeners;

use App\Events\BulkAttendanceSaved;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\StudentAttendanceSetting;
use App\Models\StudentAttendance;
use App\Services\User\AlertService;
use App\Models\Enrollment;
use App\Models\Alert;

class CheckStudentAbsenceLimit
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function handle(BulkAttendanceSaved $event): void
    {
        $setting = StudentAttendanceSetting::where('semester_id', $event->semesterId)->first();
        if (!$setting) return;

        $allowed = $setting->allowed_absence_days;
        $warningLimit = max(0, $allowed - 2);

        $unexcusedIds = collect($event->attendances)
            ->where('status', 'absent')
            ->where('absence_type', 'unexcused')
            ->pluck('enrollment_id');

        if ($unexcusedIds->isEmpty()) return;

        $absenceCounts = StudentAttendance::whereIn('enrollment_id', $unexcusedIds)
            ->where('semester_id', $event->semesterId)
            ->where('status', 'absent')
            ->where('absence_type', 'unexcused')
            ->selectRaw('enrollment_id, COUNT(*) as total_absences')
            ->groupBy('enrollment_id')
            ->get();

        foreach ($absenceCounts as $record) {
            $total = $record->total_absences;
            $enrollmentId = $record->enrollment_id;

            if ($total == $warningLimit) {
                $this->alertService->createBatchStudentAlerts(
                    [$enrollmentId],
                    Alert::TYPE_WARNING,
                    ['absence_count' => $total],
                    'تحذير: اقتراب تجاوز حد الغياب',
                    'بقي للطالب يومان فقط ويتجاوز حد الغياب المسموح.'
                );
            } elseif ($total == $allowed + 1) {
             $this->alertService->createBatchStudentAlerts(
                    [$enrollmentId],
                    Alert::TYPE_EXPULSION,
                    ['absence_count' => $total, 'law_id' => 1],
                    'تنبيه خطير: تجاوز حد الغياب المسموح',
                    "لقد تجاوز الطالب الحد المسموح للغياب غير المبرر (إجمالي الغياب: {$total} أيام)."
                );
            }
        }
    }
}
