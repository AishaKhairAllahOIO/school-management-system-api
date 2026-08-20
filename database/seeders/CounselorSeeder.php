<?php

namespace Database\Seeders;

use App\Models\CounselorAppointment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CounselorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CounselorAppointment::updateOrCreate([
            'counselor_id'=> 9,
            'student_id' => 3,
            'appointment_date' => '2026-08-19',
            'start_time' => '08:00',
            'end_time' => '08:30',
            'booking_status'=> 'cancelled'
        ]);

        CounselorAppointment::updateOrCreate([
            'counselor_id'=> 9,
            'student_id' => 2,
            'appointment_date' => '2026-08-21',
            'start_time' => '08:00',
            'end_time' => '08:30',
            'booking_status'=> 'pending'
        ]);

         CounselorAppointment::updateOrCreate([
            'counselor_id'=> 9,
            'student_id' => 2,
            'appointment_date' => '2026-08-19',
            'start_time' => '08:00',
            'end_time' => '08:30',
            'booking_status'=> 'completed',
            'slot_status' => 'booked'
        ]);
         CounselorAppointment::updateOrCreate([
            'counselor_id'=> 9,
            'student_id' => 2,
            'appointment_date' => '2026-08-20',
            'start_time' => '08:00',
            'end_time' => '08:30',
            'booking_status'=> 'accepted',
            'slot_status' => 'booked'
        ]);

    }
}
