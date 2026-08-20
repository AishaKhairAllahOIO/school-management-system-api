<?php

namespace App\Services\Counselor;

use App\Models\CounselorAvailability;
use App\Models\CounselorAppointment;
use Exception;
use App\Services\Counselor\CounselorAppointmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CounselorAvailabilityService
{

    public function __construct(
        private CounselorAppointmentService $appointmentService
    ) {
    }
    public function saveSchedule(int $counselorId, array $schedule)
    {
        return DB::transaction(function () use ($counselorId, $schedule) {

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

            $day = strtolower($day);

            $availability = CounselorAvailability::query()
                ->where('counselor_id', $counselorId)
                ->where('day', $day)
                ->lockForUpdate()
                ->first();

            if (!$availability) {
                throw new Exception(
                    'No availability schedule exists for this day.'
                );
            }

            $nextDate = $this->getNextDateForDay($day);

            $hasActiveAppointments = CounselorAppointment::query()
                ->where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $nextDate)
                ->whereIn('booking_status', [
                    'pending',
                    'accepted',
                    'completed',
                ])
                ->exists();

            if ($hasActiveAppointments) {
                throw new Exception(
                    'This day cannot be updated because it has active student appointments.'
                );
            }

            $availability->update([
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'session_duration' => $data['session_duration'],
                'daily_sessions_limit' => $data['daily_sessions_limit'],
            ]);

            CounselorAppointment::query()
                ->where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $nextDate)
                ->where('booking_status', 'available')
                ->where('slot_status', 'available')
                ->delete();

            $this->appointmentService->generateForDate($nextDate);

            return $availability->fresh();
        });
    }
    public function deleteDay(int $counselorId, string $day)
    {
        return DB::transaction(function () use ($counselorId, $day) {

            $day = strtolower($day);

            $availability = CounselorAvailability::query()
                ->where('counselor_id', $counselorId)
                ->where('day', $day)
                ->lockForUpdate()
                ->first();

            if (!$availability) {
                throw new Exception(
                    'No availability schedule exists for this day.'
                );
            }

            $nextDate = $this->getNextDateForDay($day);

            $hasActiveAppointments = CounselorAppointment::query()
                ->where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $nextDate)
                ->whereIn('booking_status', [
                    'pending',
                    'accepted',
                ])
                ->exists();

            if ($hasActiveAppointments) {
                throw new Exception(
                    'This day cannot be deleted because it has active student appointments.'
                );
            }

            CounselorAppointment::query()
                ->where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $nextDate)
                ->where('booking_status', 'available')
                ->where('slot_status', 'available')
                ->delete();

            $availability->delete();

            return true;
        });
    }
    public function addDay(int $counselorId, array $data)
    {
        return DB::transaction(function () use ($counselorId, $data) {

            $day = strtolower($data['day']);
            $exists = CounselorAvailability::query()
                ->where('counselor_id', $counselorId)
                ->where('day', $day)
                ->exists();

            if ($exists) {
                throw new Exception(
                    'An availability schedule already exists for this day.'
                );
            }


            $availability = CounselorAvailability::create([
                'counselor_id' => $counselorId,
                'day' => $day,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'session_duration' => $data['session_duration'],
                'daily_sessions_limit' => $data['daily_sessions_limit'],
            ]);

            $nextDate = $this->getNextDateForDay($day);

            $this->appointmentService->generateForDate($nextDate);

            return $availability->fresh();
        });
    }
    private function getNextDateForDay(string $day): Carbon
    {
        $days = [
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ];

        if (!in_array($day, $days, true)) {
            throw new Exception(
                'The selected day is invalid.'
            );
        }

        $today = Carbon::today();

        $targetDayIndex = array_search($day, $days, true);

        $daysUntilTarget = (
            $targetDayIndex - $today->dayOfWeek + 7
        ) % 7;

        if ($daysUntilTarget === 0) {
            $daysUntilTarget = 7;
        }

        return $today->copy()->addDays($daysUntilTarget);
    }
}
