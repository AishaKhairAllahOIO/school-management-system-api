<?php

namespace App\Jobs;

use App\Domain\Scheduling\Actions\GenerateScheduleAction;
use App\Services\User\AlertService;
use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(
        public int $academicYearId,
        public int $termId,
        public int $staffId
    ) {}

    public function handle(GenerateScheduleAction $action, AlertService $alertService): void
    {
        $staff = Staff::find($this->staffId);

        try {
            Log::info("Starting schedule generation for Year {$this->academicYearId}, Term {$this->termId}");

            $schedule = $action->execute($this->academicYearId, $this->termId);

            if ($staff) {
                $alertService->createSystemNotice(
                    $staff,
                    'Schedule Generated ✅',
                    'The timetable has been successfully generated and is ready for review.',
                    ['action' => 'schedule_generated']
                );
            }

        } catch (Exception $e) {
            Log::error("Schedule generation failed: " . $e->getMessage());

            if ($staff) {
                $alertService->createSystemNotice(
                    $staff,
                    'Generation Failed ❌',
                    'The engine failed to generate the schedule due to extreme constraints. Please review teacher workloads.',
                    ['action' => $e->getMessage()]
                );
            }
            throw $e;
        }
    }
}
