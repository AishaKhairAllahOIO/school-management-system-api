<?php

namespace App\Console\Commands;

use App\Services\Counselor\CounselorAppointmentService;
use Illuminate\Console\Command;

class GenerateTomorrowCounselorAppointments extends Command
{
    protected $signature = 'counselor:generate-tomorrow-appointments';

    protected $description = 'Generate counselor appointments for tomorrow';

    public function handle(
        CounselorAppointmentService $service
    ): int {
        $count = $service->generateForTomorrow();

        $this->info(
            "Generated {$count} counselor appointments for tomorrow."
        );

        return self::SUCCESS;
    }
}
