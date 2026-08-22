<?php

namespace App\Console\Commands;

use App\Models\CounselorAppointment;
use App\Models\CounselingSession;
use App\Services\Counselor\CounselorAppointmentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteExpiredCounselingAppointments extends Command
{
    protected $signature = 'counselor:complete-expired-appointments';

    protected $description = 'Complete counseling appointments after their end time';

   public function handle(
    CounselorAppointmentService $service
): int {

    $completed = $service->completeExpiredAppointments();

    $this->info(
        "Completed {$completed} counseling appointments."
    );

    return self::SUCCESS;
}
}
