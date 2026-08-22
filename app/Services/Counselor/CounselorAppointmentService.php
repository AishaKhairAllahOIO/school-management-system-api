<?php

namespace App\Services\Counselor;

use App\Jobs\SendPushNotification;
use App\Models\AcademicSetting;
use App\Models\Alert;
use App\Models\CounselorAppointment;
use App\Models\CounselorAvailability;
use App\Models\Student;
use App\Services\User\AlertService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CounselorAppointmentService
{
    public function __construct(private AlertService $alertService)
    {
    }

    public function generateForTomorrow(): int
    {
        $date = $this->getNextAvailableWorkingDate();

        return $this->generateForDate($date);
    }
    public function generateForDate(Carbon|string $date): int
    {
        return DB::transaction(function () use ($date) {

            $date = $date instanceof Carbon
                ? $date->copy()->startOfDay()
                : Carbon::parse($date)->startOfDay();


            $day = strtolower($date->englishDayOfWeek);


            $availabilities = CounselorAvailability::query()

                ->where('day', $day)

                ->where('is_active', true)

                ->get();


            if ($availabilities->isEmpty()) {
                return 0;
            }


            $insertData = [];

            $now = now();



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


                if ($duration <= 0) {
                    continue;
                }



                while (
                    $start->copy()
                        ->addMinutes($duration)
                        ->lte($end)
                ) {


                    $insertData[] = [

                        'counselor_id' =>
                            $availability->counselor_id,


                        'student_id' => null,


                        'appointment_date' =>
                            $date->toDateString(),


                        'start_time' =>
                            $start->format('H:i:s'),


                        'end_time' =>
                            $start
                                ->copy()
                                ->addMinutes($duration)
                                ->format('H:i:s'),


                        'booking_status' =>
                            'available',


                        'slot_status' =>
                            'available',


                        'created_at' =>
                            $now,


                        'updated_at' =>
                            $now,

                    ];


                    $start->addMinutes($duration);

                }

            }



            if (empty($insertData)) {
                return 0;
            }



            $existingSlots = CounselorAppointment::query()

                ->whereDate(
                    'appointment_date',
                    $date->toDateString()
                )

                ->whereIn(
                    'counselor_id',
                    collect($insertData)
                        ->pluck('counselor_id')
                        ->unique()
                )

                ->get([
                    'counselor_id',
                    'appointment_date',
                    'start_time'
                ])

                ->map(function ($item) {

                    return implode('|', [
                        $item->counselor_id,
                        $item->appointment_date->toDateString(),
                        $item->start_time
                    ]);

                })

                ->flip();



            $newSlots = collect($insertData)

                ->filter(function ($slot) use ($existingSlots) {


                    $key = implode('|', [

                        $slot['counselor_id'],

                        $slot['appointment_date'],

                        $slot['start_time']

                    ]);


                    return !$existingSlots->has($key);

                })

                ->values()

                ->toArray();



            if (empty($newSlots)) {
                return 0;
            }



            CounselorAppointment::insert($newSlots);


            return count($newSlots);

        });
    }
    public function getAvailableTomorrowSlots()
    {
        $date = $this->getNextAvailableWorkingDate();

        if (!$date) {
            return collect([]);
        }

        $day = strtolower(
            $date->englishDayOfWeek
        );

        return CounselorAppointment::query()

            ->whereDate(
                'appointment_date',
                $date->toDateString()
            )

            ->where(
                'booking_status',
                'available'
            )

            ->where(
                'slot_status',
                'available'
            )

            ->whereHas(
                'counselor',
                function ($query) use ($day) {

                    $query->whereHas(
                        'availabilities',
                        function ($q) use ($day) {

                            $q->where('day', $day)
                                ->where('is_active', true);

                        }
                    );

                }
            )

            ->orderBy('start_time')

            ->get([
                'id',
                'appointment_date',
                'start_time',
                'end_time',
                'booking_status'
            ]);
    }
    public function bookAppointment(int $studentId, string $appointmentDate, string $startTime, string $endTime): CounselorAppointment
    {

        return DB::transaction(function () use ($studentId, $appointmentDate, $startTime, $endTime) {
            try {

                $appointmentDate = Carbon::parse($appointmentDate)
                    ->toDateString();

                $requestedStart = Carbon::createFromFormat(
                    'H:i',
                    $startTime
                );


                $requestedEnd = Carbon::createFromFormat(
                    'H:i',
                    $endTime
                );


            } catch (\Throwable $e) {

                throw new Exception(
                    'The appointment date or time is invalid.'
                );

            }


            if ($requestedStart->gte($requestedEnd)) {

                throw new Exception(
                    'The appointment start time must be before the end time.'
                );

            }


            $appointmentDateTime = Carbon::parse(
                $appointmentDate
                . ' '
                . $requestedStart->format('H:i:s')
            );


            if ($appointmentDateTime->lte(now())) {

                throw new Exception(
                    'You cannot book an appointment that has already started.'
                );

            }

            $studentHasAppointment = CounselorAppointment::query()

                ->where(
                    'student_id',
                    $studentId
                )

                ->whereDate(
                    'appointment_date',
                    $appointmentDate
                )

                ->whereIn(
                    'booking_status',
                    [
                        'pending',
                        'accepted'
                    ]
                )

                ->where(
                    'start_time',
                    '<',
                    $requestedEnd->format('H:i:s')
                )

                ->where(
                    'end_time',
                    '>',
                    $requestedStart->format('H:i:s')
                )

                ->exists();


            if ($studentHasAppointment) {

                throw new Exception(
                    'You already have another appointment at this time.'
                );

            }


            $appointments = CounselorAppointment::query()

                ->whereDate(
                    'appointment_date',
                    $appointmentDate
                )

                ->where(
                    'start_time',
                    $requestedStart->format('H:i:s')
                )

                ->where(
                    'end_time',
                    $requestedEnd->format('H:i:s')
                )

                ->where(
                    'booking_status',
                    'available'
                )

                ->where(
                    'slot_status',
                    'available'
                )

                ->lockForUpdate()

                ->get();



            if ($appointments->isEmpty()) {

                throw new Exception(
                    'This appointment slot is no longer available.'
                );

            }


            $day = strtolower(
                Carbon::parse($appointmentDate)
                    ->englishDayOfWeek
            );



            $counselorIds = $appointments
                ->pluck('counselor_id')
                ->unique()
                ->values();



            $availabilities = CounselorAvailability::query()

                ->whereIn(
                    'counselor_id',
                    $counselorIds
                )

                ->where(
                    'day',
                    $day
                )

                ->where(
                    'is_active',
                    true
                )

                ->get()

                ->keyBy('counselor_id');




            $acceptedCounts = CounselorAppointment::query()

                ->selectRaw(
                    'counselor_id, COUNT(*) as count'
                )

                ->whereIn(
                    'counselor_id',
                    $counselorIds
                )

                ->whereDate(
                    'appointment_date',
                    $appointmentDate
                )

                ->where(
                    'booking_status',
                    'accepted'
                )

                ->groupBy(
                    'counselor_id'
                )

                ->pluck(
                    'count',
                    'counselor_id'
                );




            $selectedAppointment = null;

            $lowestLoad = PHP_INT_MAX;



            foreach ($appointments as $appointment) {


                $availability = $availabilities
                    ->get(
                        $appointment->counselor_id
                    );


                if (!$availability) {
                    continue;
                }


                $slotStart = Carbon::createFromFormat(
                    'H:i:s',
                    $appointment->start_time
                );


                $slotEnd = Carbon::createFromFormat(
                    'H:i:s',
                    $appointment->end_time
                );


                $workStart = Carbon::createFromFormat(
                    'H:i:s',
                    $availability->start_time
                );


                $workEnd = Carbon::createFromFormat(
                    'H:i:s',
                    $availability->end_time
                );

                if (
                    $slotStart->lt($workStart)
                    ||
                    $slotEnd->gt($workEnd)
                ) {

                    continue;

                }

                $acceptedCount = (int)
                    $acceptedCounts->get(
                        $appointment->counselor_id,
                        0
                    );

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
                    'No counselor is currently available for this appointment slot.'
                );

            }

            $selectedAppointment->update([

                'student_id' => $studentId,

                'booking_status' => 'pending',

                'slot_status' => 'booked',

            ]);
            $selectedAppointment->load([
                'student.user',
                'counselor.user'
            ]);
            if ($selectedAppointment->counselor) {
                $studentName = trim(
                    $selectedAppointment->student->user->first_name
                    . ' '
                    .
                    $selectedAppointment->student->user->last_name
                );
                // SendPushNotification::dispatch(
                //     $selectedAppointment->counselor->id,
                //     'appointment request',
                //     'there is new appointment request please check the pending list',
                //     [
                //         'type' => 'material',
                //         'material_type' => (string) $material->type,
                //     ]
                // )->afterCommit();
            }
            return $selectedAppointment;

        });

    }
    public function approveAppointment(int $counselorId, int $appointmentId)
    {
        return DB::transaction(function () use ($counselorId, $appointmentId) {


            $appointment = CounselorAppointment::query()
                ->where('id', $appointmentId)
                ->where('counselor_id', $counselorId)
                ->where('booking_status', 'pending')
                ->lockForUpdate()
                ->first();



            if (!$appointment) {

                throw new Exception(
                    'Appointment does not exist or is not pending.'
                );

            }


            $appointmentStart = Carbon::parse(
                $appointment->appointment_date
                    ->format('Y-m-d')
                . ' '
                .
                $appointment->start_time
            );



            if ($appointmentStart->lte(now())) {

                throw new Exception(
                    'Cannot approve an appointment that has already started.'
                );

            }


            $day = strtolower(
                Carbon::parse(
                    $appointment->appointment_date
                )
                    ->englishDayOfWeek
            );

            $availability = CounselorAvailability::query()

                ->where(
                    'counselor_id',
                    $counselorId
                )

                ->where(
                    'day',
                    $day
                )

                ->where(
                    'is_active',
                    true
                )

                ->first();

            if (!$availability) {

                throw new Exception(
                    'Counselor is not available on this day.'
                );

            }

            $acceptedCount = CounselorAppointment::query()

                ->where(
                    'counselor_id',
                    $counselorId
                )

                ->whereDate(
                    'appointment_date',
                    $appointment->appointment_date
                )

                ->where(
                    'booking_status',
                    'accepted'
                )

                ->lockForUpdate()

                ->count();



            if (
                $acceptedCount >=
                $availability->daily_sessions_limit
            ) {

                throw new Exception(
                    'Daily counseling session limit reached.'
                );

            }




            /*
            |--------------------------------------------------------------------------
            | قبول الموعد
            |--------------------------------------------------------------------------
            */


            $appointment->update([

                'booking_status' => 'accepted',

                'slot_status' => 'booked',

            ]);




            /*
            |--------------------------------------------------------------------------
            | إشعار الطالب
            |--------------------------------------------------------------------------
            */


            $appointment->load([
                'student.user'
            ]);



            if ($appointment->student) {


                $academicSetting =
                    AcademicSetting::first();



                if (
                    $academicSetting
                    &&
                    $academicSetting->current_academic_year_id
                ) {


                    $enrollment =
                        $appointment->student
                            ->enrollments()
                            ->where(
                                'academic_year_id',
                                $academicSetting
                                    ->current_academic_year_id
                            )
                            ->where(
                                'enrollment_status',
                                'enrolled'
                            )
                            ->first();


                    if ($enrollment) {


                        $this->alertService
                            ->createStudentOnlyAlert(

                                $enrollment,

                                Alert::TYPE_COUNSELING_RESPONSE,

                                'Appointment accepted',

                                'Your counseling appointment has been accepted.',

                                [

                                    'status' => 'accepted',

                                    'appointment_id' =>
                                        $appointment->id,

                                    'appointment_date' =>
                                        $appointment
                                            ->appointment_date
                                            ->toDateString(),

                                    'start_time' =>
                                        $appointment->start_time,

                                    'end_time' =>
                                        $appointment->end_time,

                                ]

                            );

                    }

                }

            }

            return $appointment->fresh();

        });
    }
    public function getPendingCounselorAppointments(int $counselorId, ?string $date = null)
    {
        if ($date) {

            $date = Carbon::parse($date)->toDateString();

        } else {

            $workingDate = $this->getNextAvailableWorkingDate();

            if (!$workingDate) {
                return collect([]);
            }

            $date = $workingDate->toDateString();
        }


        return CounselorAppointment::query()

            ->where(
                'counselor_id',
                $counselorId
            )

            ->whereDate(
                'appointment_date',
                $date
            )

            ->where(
                'booking_status',
                'pending'
            )

            ->with([
                'student.user',
            ])

            ->orderBy(
                'start_time'
            )

            ->get();
    }
    public function getStudentAppointments(int $studentId)
    {
        return CounselorAppointment::query()
            ->where('student_id', $studentId)
            ->whereIn('booking_status', [
                'pending',
                'accepted',
                'completed',
                'cancelled',
                'not_available'
            ])
            ->with([
                'counselor.user',
            ])
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->get();
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
                !in_array(
                    $appointment->booking_status,
                    [
                        'pending',
                        'accepted',
                    ]
                )
            ) {
                throw new Exception(
                    'لا يمكن إلغاء هذا الموعد في حالته الحالية.'
                );
            }

            $appointmentStart = Carbon::parse(
                $appointment
                    ->appointment_date
                    ->format('Y-m-d')
                . ' '
                . $appointment->start_time
            );

            if (now()->gte($appointmentStart)) {
                throw new Exception(
                    'لا يمكن إلغاء الموعد بعد بدء الجلسة.'
                );
            }

            $appointment->update([
                'student_id' => null,
                'booking_status' => 'available',
                'slot_status' => 'available',
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
                    'Appointment does not existe or you do not have the permission to cancel it.'
                );
            }

            if (!in_array($appointment->booking_status, ['pending', 'accepted'])) {
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

            $student = $appointment->student;

            $appointment->update([
                'student_id' => null,
                'booking_status' => 'available',
                'slot_status' => 'available',
            ]);

            if ($student?->user) {
                SendPushNotification::dispatch(
                    [$student->user->id],
                    'Counseling appointment cancelled',
                    'Your counselor has cancelled your counseling appointment.',
                    [
                        'type' => 'counseling_appointment_cancelled',
                    ]
                )->afterCommit();
            }

            return $appointment->fresh();
        });
    }
    public function getCounselorStudents(int $counselorId, ?int $gradeLevelId = null, ?string $search = null)
    {
        return Student::query()

            ->whereHas('counselorAppointments', function ($query) use ($counselorId) {

                $query->where('counselor_id', $counselorId)
                    ->where('booking_status', 'completed');

            })


            ->when($gradeLevelId, function ($query) use ($gradeLevelId) {

                $query->whereHas('enrollments', function ($q) use ($gradeLevelId) {

                    $q->whereHas('classRoom', function ($class) use ($gradeLevelId) {
                        $class->where('grade_level_id', $gradeLevelId);
                    });

                });

            })

            ->when($search, function ($query) use ($search) {

                $query->whereHas('user', function ($q) use ($search) {

                    $q->where(function ($name) use ($search) {

                        $name->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");

                    });

                });

            })
            ->with([
                'user',
                'enrollments.classRoom.gradeLevel',
                'counselorAppointments' => function ($query) use ($counselorId) {

                    $query->where('counselor_id', $counselorId)
                        ->where('booking_status', 'completed')
                        ->with('counselingSession')
                        ->latest('appointment_date');

                }
            ])

            ->withCount([
                'counselorAppointments as sessions_count' => function ($query) use ($counselorId) {

                    $query->where('counselor_id', $counselorId)
                        ->where('booking_status', 'completed');

                }
            ])

            ->get();
    }
    public function getStudentSessions(int $studentId, int $counselorId)
    {


        return CounselorAppointment::query()

            ->where(
                'student_id',
                $studentId
            )

            ->where(
                'counselor_id',
                $counselorId
            )


            ->with([
                'counselingSession'
            ])

            ->orderByDesc(
                'appointment_date'
            )

            ->orderByDesc(
                'start_time'
            )

            ->get();

    }
    public function getTomorrowSchedule(int $counselorId)
    {
        $date = $this->getNextAvailableWorkingDate();


        return CounselorAppointment::query()

            ->where(
                'counselor_id',
                $counselorId
            )

            ->whereDate(
                'appointment_date',
                $date->toDateString()
            )

            ->with([
                'student.user'
            ])

            ->orderBy(
                'start_time'
            )

            ->get();
    }
    public function getAcceptedCounselorAppointments(int $counselorId, ?string $date = null)
    {
        if ($date) {

            $date = Carbon::parse($date)->toDateString();

        } else {

            $workingDate = $this->getNextAvailableWorkingDate();

            if (!$workingDate) {
                return collect([]);
            }

            $date = $workingDate->toDateString();
        }


        return CounselorAppointment::query()

            ->where('counselor_id', $counselorId)

            ->whereDate('appointment_date', $date)

            ->where('booking_status', 'accepted')

            ->whereNotNull('student_id')

            ->with([
                'student.user',
            ])

            ->orderBy('start_time')

            ->get();
    }
    private function getNextAvailableWorkingDate(): ?Carbon
    {
        $date = Carbon::tomorrow();

        for ($i = 0; $i < 7; $i++) {

            $day = strtolower(
                $date->englishDayOfWeek
            );

            $exists = CounselorAvailability::query()
                ->where('day', $day)
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return $date->copy();
            }

            $date->addDay();
        }

        return null;
    }

}
