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

            // تحقق من أن التعديل لن يضر بالحجوزات النشطة
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
        $hasActiveAppointments = CounselorAppointment::where('counselor_id', $counselorId)
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereRaw("LOWER(DAYNAME(appointment_date)) = ?", [$day])
            ->whereIn('booking_status', ['pending', 'accepted'])
            ->exists();

        if ($hasActiveAppointments) {
            throw new Exception('لا يمكن حذف توافر هذا اليوم لوجود حجوزات نشطة للطلاب.');
        }

        return CounselorAvailability::where('counselor_id', $counselorId)
            ->where('day', $day)
            ->delete();
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
