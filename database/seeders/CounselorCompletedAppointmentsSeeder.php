<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CounselorAppointment;
use App\Models\CounselingSession;
use Carbon\Carbon;

class CounselorCompletedAppointmentsSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = [
            [
                'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'start' => '09:00',
                'end' => '09:30',
            ],
            [
                'date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'start' => '10:00',
                'end' => '10:30',
            ],
            [
                'date' => Carbon::now()->subDay()->format('Y-m-d'),
                'start' => '11:00',
                'end' => '11:30',
            ],
        ];


        foreach ($appointments as $item) {

            $appointment = CounselorAppointment::create([

                'counselor_id' => 9,

                'student_id' => 2,

                'appointment_date' => $item['date'],

                'start_time' => $item['start'],

                'end_time' => $item['end'],

                'booking_status' => 'completed',

                'slot_status' => 'booked',

            ]);


            CounselingSession::create([

                'appointment_id' => $appointment->id,

                'attendance_status' => 'not_marked',

                'assessment' => null,

                'notes' => null,

            ]);
        }
    }
}