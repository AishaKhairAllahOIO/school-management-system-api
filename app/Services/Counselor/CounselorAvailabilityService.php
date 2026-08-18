<?php

namespace App\Services\Counselor;

use App\Models\CounselorAvailability;
use App\Models\CounselorAppointment;
use Exception;
use Illuminate\Support\Facades\DB;

class CounselorAvailabilityService
{
    public function saveSchedule(int $counselorId, array $schedule)
    {
        return DB::transaction(function () use ($counselorId, $schedule) {

            // تحقق من عدم وجود حجوزات مستقبلية قبل مسح الجدول
            $hasFutureAppointments = CounselorAppointment::where('counselor_id', $counselorId)
                ->where('appointment_date', '>=', now()->toDateString())
                ->whereIn('booking_status', ['pending', 'accepted'])
                ->exists();

            if ($hasFutureAppointments) {
                throw new Exception('لا يمكن مسح الجدول بالكامل لوجود حجوزات نشطة للطلاب في الأيام القادمة. يرجى تعديل الأيام بشكل فردي.');
            }

            CounselorAvailability::where('counselor_id', $counselorId)->delete();

            $data = [];
            foreach ($schedule as $item) {
                $data[] = [
                    'counselor_id' => $counselorId,
                    'day' => $item['day'],
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                    'session_duration' => $item['session_duration'],
                    'daily_sessions_limit' => $item['daily_sessions_limit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            return CounselorAvailability::insert($data);
        });
    }

    public function getSchedule(int $counselorId)
    {
        return CounselorAvailability::where('counselor_id', $counselorId)
            ->orderBy('day')
            ->get();
    }

    public function updateDay(int $counselorId, string $day, array $data)
    {
        return DB::transaction(function () use ($counselorId, $day, $data) {
            $availability = CounselorAvailability::where('counselor_id', $counselorId)
                ->where('day', $day)
                ->firstOrFail();

            $activeAppointmentsCount = CounselorAppointment::where('counselor_id', $counselorId)
                ->where('appointment_date', '>=', now()->toDateString())
                ->whereRaw("LOWER(DAYNAME(appointment_date)) = ?", [$day])
                ->whereIn('booking_status', ['pending', 'accepted'])
                ->count();

            if ($activeAppointmentsCount > 0 && isset($data['daily_sessions_limit'])) {
                if ($data['daily_sessions_limit'] < $activeAppointmentsCount) {
                    throw new Exception('لا يمكن تقليل حد الجلسات اليومي، لأن لديك بالفعل ' . $activeAppointmentsCount . ' حجوزات نشطة في هذا اليوم.');
                }
            }

            $availability->update($data);

            return $availability;
        });
    }

    public function deleteDay(int $counselorId, string $day)
    {
        return DB::transaction(function () use ($counselorId, $day) {

            $availability = CounselorAvailability::where('counselor_id', $counselorId)
                ->where('day', $day)
                ->first();

            if (!$availability) {
                throw new Exception(
                    'لا يوجد جدول توافر لهذا اليوم.'
                );
            }


            // البحث عن الحجوزات القادمة في هذا اليوم
            $hasActiveAppointments = CounselorAppointment::where('counselor_id', $counselorId)
                ->whereIn('booking_status', [
                    'pending',
                    'accepted'
                ])
                ->where('appointment_date', '>=', now()->toDateString())
                ->where(function ($query) use ($day) {

                    $days = [
                        'sunday' => 0,
                        'monday' => 1,
                        'tuesday' => 2,
                        'wednesday' => 3,
                        'thursday' => 4,
                        'friday' => 5,
                        'saturday' => 6,
                    ];


                    if (!isset($days[$day])) {
                        return;
                    }


                    $query->whereRaw(
                        'DAYOFWEEK(appointment_date) = ?',
                        [
                            $days[$day] + 1
                        ]
                    );

                })
                ->exists();


            if ($hasActiveAppointments) {

                throw new Exception(
                    'لا يمكن حذف هذا اليوم لأنه يحتوي على حجوزات طلاب نشطة.'
                );

            }


            return $availability->delete();

        });
    }

    public function addDay(int $counselorId, array $data)
    {
        return DB::transaction(function () use ($counselorId, $data) {

            $exists = CounselorAvailability::where('counselor_id', $counselorId)
                ->where('day', $data['day'])
                ->exists();

            if ($exists) {
                throw new Exception(
                    'يوجد جدول توافر لهذا اليوم مسبقاً.'
                );
            }


            return CounselorAvailability::create([
                'counselor_id' => $counselorId,
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'session_duration' => $data['session_duration'],
                'daily_sessions_limit' => $data['daily_sessions_limit'],
            ]);

        });
    }
}
