<?php

namespace App\Console\Commands;

use App\Models\CounselorAppointment;
use App\Models\CounselingSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteExpiredCounselingAppointments extends Command
{
    protected $signature = 'counselor:complete-expired-appointments';

    protected $description = 'Complete counseling appointments after their end time';

    public function handle(): int
    {
        $completed = 0;

        CounselorAppointment::query()
            ->where('booking_status', 'accepted')
            ->whereDate(
                'appointment_date',
                '<=',
                now()->toDateString()
            )
            ->chunkById(100, function ($appointments) use (&$completed) {

                foreach ($appointments as $appointment) {

                    $endDateTime = Carbon::parse(
                        $appointment->appointment_date->format('Y-m-d')
                        . ' '
                        . $appointment->end_time
                    );

                    if (now()->lt($endDateTime)) {
                        continue;
                    }

                    DB::transaction(function () use (
                        $appointment,
                        &$completed
                    ) {

                        $appointment->update([
                            'booking_status' => 'completed',
                        ]);

                        CounselingSession::firstOrCreate(
                            [
                                'appointment_id' => $appointment->id,
                            ],
                            [
                                'attendance_status' => 'not_marked',
                            ]
                        );

                        $completed++;
                    });
                }
            });

        $this->info(
            "Completed {$completed} counseling appointments."
        );

        return self::SUCCESS;
    }
}