<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\Semester;
use App\Models\Staff;
use App\Services\User\AlertService;
use Illuminate\Console\Command;

class NotifyEndSemesterExpulsions extends Command
{
    protected $signature = 'school:notify-expulsions';
    protected $description = 'To cheack the end of the semester to send notification.';
    public function handle(AlertService $alertService)
    {
        $today = now()->toDateString();

        $endingSemester = Semester::where('is_current', true)
            ->where('end_date', $today)
            ->first();

        if (!$endingSemester) {
            $this->info('Today not the end of the semester.');
            return;
        }

        $expulsionCount = Enrollment::whereHas('alerts', function ($query) {
            $query->where('type', Alert::TYPE_EXPULSION);
        })->count();

        if ($expulsionCount === 0) {
            $this->info('There is no student in the expulsion list.');
            return;
        }

        $admins = Staff::whereHas('user', function ($q) {
            $q->whereHas('permissions', function ($rq) {
                $rq->where('name', 'account:toggle_status');
            });
        })->get();

        foreach ($admins as $admin) {
            $alertService->createStaffAlerts([
                'staff_ids' => [$admin->id],
                'type' => 'system_notice',
                'title' => 'إجراء مطلوب: مراجعة قرارات الفصل',
                'description' => "انتهى الفصل الدراسي. يوجد {$expulsionCount} طالب(ة) استحقوا قرار الفصل بسبب الغياب أو المخالفات. يرجى الدخول لمراجعة القائمة واعتماد التعطيل.",
                'meta' => ['action' => 'review_expulsions']
            ]);
        }

        $this->info('Notification sent successfuly to the dashbourd.');
    }
}
