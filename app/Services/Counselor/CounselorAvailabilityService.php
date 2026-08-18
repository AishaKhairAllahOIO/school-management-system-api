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
