<?php

namespace App\Services\Counselor;

use App\Models\CounselorAppointment;
use App\Models\CounselorAvailability;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CounselorAppointmentService
{

    public function generateForTomorrow(): int
    {
        return DB::transaction(function () {

            $tomorrow = Carbon::tomorrow();

            $day = strtolower($tomorrow->englishDayOfWeek);

            $availabilities = CounselorAvailability::query()
                ->where('day', $day)
                ->where('is_active', true)
                ->get();

            $createdCount = 0;

            foreach ($availabilities as $availability) {

                $start = Carbon::createFromFormat(
                    'H:i:s',
                    $availability->start_time
                );

                $end = Carbon::createFromFormat(
                    'H:i:s',
                    $availability->end_time
                );

                $duration = (int) $availability->session_duration;

                while (
                    $start->copy()
                        ->addMinutes($duration)
                        ->lte($end)
                ) {

                    $slotStart = $start->format('H:i:s');

                    $slotEnd = $start
                        ->copy()
                        ->addMinutes($duration)
                        ->format('H:i:s');

                    $exists = CounselorAppointment::query()
                        ->where('counselor_id', $availability->counselor_id)
                        ->whereDate(
                            'appointment_date',
                            $tomorrow->toDateString()
                        )
                        ->where('start_time', $slotStart)
                        ->exists();

                    if (!$exists) {

                        CounselorAppointment::create([
                            'counselor_id' => $availability->counselor_id,
                            'student_id' => null,
                            'appointment_date' => $tomorrow->toDateString(),
                            'start_time' => $slotStart,
                            'end_time' => $slotEnd,
                            'booking_status' => 'available',
                        ]);

                        $createdCount++;
                    }

                    $start->addMinutes($duration);
                }
            }

            return $createdCount;
        });
    }
    public function getAvailableTomorrowSlots()
    {
        return CounselorAppointment::query()
            ->whereDate(
                'appointment_date',
                Carbon::tomorrow()->toDateString()
            )
            ->where('booking_status', 'available')
            ->orderBy('start_time')
            ->get([
                'appointment_date',
                'start_time',
                'end_time',
                'booking_status',
            ])
            ->unique(function ($appointment) {
                return
                    $appointment->appointment_date->toDateString()
                    . '|'
                    . $appointment->start_time
                    . '|'
                    . $appointment->end_time
                    . '|'
                    . $appointment->booking_status
                    . '|'
                    . $appointment->id;
            })
            ->values();
    }
    public function bookAppointment(int $studentId, string $appointmentDate, string $startTime, string $endTime): CounselorAppointment
    {

        return DB::transaction(function () use ($studentId, $appointmentDate, $startTime, $endTime) {

            $studentAlreadyBooked = CounselorAppointment::query()
                ->where('student_id', $studentId)
                ->whereDate('appointment_date', $appointmentDate)
                ->where('start_time', $startTime)
                ->whereIn('booking_status', [
                    'pending',
                    'accepted',
                ])
                ->exists();

            if ($studentAlreadyBooked) {
                throw new Exception(
                    'لديك بالفعل موعد في هذا الوقت.'
                );
            }



            $appointments = CounselorAppointment::query()
                ->whereDate('appointment_date', $appointmentDate)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->where('booking_status', 'available')
                ->lockForUpdate()
                ->get();


            if ($appointments->isEmpty()) {
                throw new Exception(
                    'هذا الموعد لم يعد متاحاً.'
                );
            }



            $selectedAppointment = null;

            $lowestLoad = PHP_INT_MAX;


            foreach ($appointments as $appointment) {

                $availability = CounselorAvailability::query()
                    ->where('counselor_id', $appointment->counselor_id)
                    ->where(
                        'day',
                        strtolower(
                            Carbon::parse($appointmentDate)
                                ->englishDayOfWeek
                        )
                    )
                    ->where('is_active', true)
                    ->first();


                if (!$availability) {
                    continue;
                }



                $acceptedCount = CounselorAppointment::query()
                    ->where(
                        'counselor_id',
                        $appointment->counselor_id
                    )
                    ->whereDate(
                        'appointment_date',
                        $appointmentDate
                    )
                    ->where('booking_status', 'accepted')
                    ->count();

                if (
                    $acceptedCount >=
                    $availability->daily_sessions_limit
                ) {
                    continue;
                }


                if ($acceptedCount < $lowestLoad) {

                    $lowestLoad = $acceptedCount;

                    $selectedAppointment = $appointment;
                }
            }


            if (!$selectedAppointment) {
                throw new Exception(
                    'لا يوجد مرشد متاح لهذا الموعد حالياً.'
                );
            }


            $selectedAppointment->update([
                'student_id' => $studentId,
                'booking_status' => 'pending',
            ]);


            return $selectedAppointment->fresh([
                'student',
                'counselor.user',
            ]);
        });
    }

    public function cancelByStudent(int $appointmentId, int $studentId): CounselorAppointment
    {

        return DB::transaction(function () use ($appointmentId, $studentId) {

            $appointment = CounselorAppointment::query()
                ->where('id', $appointmentId)
                ->where('student_id', $studentId)
                ->lockForUpdate()
                ->first();

            if (!$appointment) {
                throw new Exception(
                    'الموعد غير موجود أو لا تملك صلاحية إلغائه.'
                );
            }

            if (
                !in_array($appointment->booking_status, [
                    'pending',
                    'accepted',
                ])
            ) {
                throw new Exception(
                    'لا يمكن إلغاء هذا الموعد في حالته الحالية.'
                );
            }

            $appointmentStart = Carbon::parse(
                $appointment->appointment_date->format('Y-m-d')
                . ' '
                . $appointment->start_time
            );

            if (now()->gte($appointmentStart)) {
                throw new Exception(
                    'لا يمكن إلغاء الموعد بعد بدء الجلسة.'
                );
            }

            $appointment->update([
                'booking_status' => 'cancelled',
            ]);

            return $appointment->fresh();
        });
    }

    public function cancelByCounselor(int $appointmentId, int $counselorId): CounselorAppointment
    {

        return DB::transaction(function () use ($appointmentId, $counselorId) {

            $appointment = CounselorAppointment::query()
                ->where('id', $appointmentId)
                ->where('counselor_id', $counselorId)
                ->lockForUpdate()
                ->first();

            if (!$appointment) {
                throw new Exception(
                    'الموعد غير موجود أو لا تملك صلاحية إلغائه.'
                );
            }

            if (
                !in_array($appointment->booking_status, [
                    'pending',
                    'accepted',
                ])
            ) {
                throw new Exception(
                    'لا يمكن إلغاء هذا الموعد في حالته الحالية.'
                );
            }

            $appointmentStart = Carbon::parse(
                $appointment->appointment_date->format('Y-m-d')
                . ' '
                . $appointment->start_time
            );

            if (now()->gte($appointmentStart)) {
                throw new Exception(
                    'لا يمكن إلغاء الموعد بعد بدء الجلسة.'
                );
            }

            $appointment->update([
                'booking_status' => 'cancelled',
            ]);

            return $appointment->fresh();
        });
    }


}