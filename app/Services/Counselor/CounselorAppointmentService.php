<?php

namespace App\Services\Counselor;

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
        return $this->generateForDate(Carbon::tomorrow());
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
                        'counselor_id' => $availability->counselor_id,
                        'student_id' => null,
                        'appointment_date' => $date->toDateString(),
                        'start_time' => $start->format('H:i:s'),
                        'end_time' => $start
                            ->copy()
                            ->addMinutes($duration)
                            ->format('H:i:s'),
                        'booking_status' => 'available',
                        'slot_status' => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $start->addMinutes($duration);
                }
            }

            if (empty($insertData)) {
                return 0;
            }

            $counselorIds = collect($insertData)
                ->pluck('counselor_id')
                ->unique()
                ->values();

            $existingSlots = CounselorAppointment::query()
                ->whereDate('appointment_date', $date->toDateString())
                ->whereIn('counselor_id', $counselorIds)
                ->get([
                    'counselor_id',
                    'appointment_date',
                    'start_time',
                ])
                ->map(function ($appointment) {
                    return implode('|', [
                        $appointment->counselor_id,
                        $appointment->appointment_date->toDateString(),
                        $appointment->start_time,
                    ]);
                })
                ->flip();


            $newSlots = collect($insertData)
                ->filter(function (array $slot) use ($existingSlots) {

                    $key = implode('|', [
                        $slot['counselor_id'],
                        $slot['appointment_date'],
                        $slot['start_time'],
                    ]);

                    return !$existingSlots->has($key);
                })
                ->values()
                ->all();

            if (empty($newSlots)) {
                return 0;
            }

            CounselorAppointment::insertOrIgnore($newSlots);


            return count($newSlots);
        });
    }

    public function getAvailableTomorrowSlots()
    {
        $date = Carbon::now()->addDay()->toDateString();

        return CounselorAppointment::query()
            ->whereDate('appointment_date', $date)
            ->where('booking_status', 'available')
            ->where('slot_status', 'available')
            ->orderBy('start_time')
            ->get([
                'id',
                'appointment_date',
                'start_time',
                'end_time',
                'booking_status',
            ]);
    }

    public function bookAppointment(int $studentId, string $appointmentDate, string $startTime, string $endTime): CounselorAppointment
    {
        return DB::transaction(function () use ($studentId, $appointmentDate, $startTime, $endTime) {


            try {
                $appointmentDate = Carbon::parse($appointmentDate)->toDateString();

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

            $tomorrow = Carbon::tomorrow()->toDateString();

            if ($appointmentDate !== $tomorrow) {
                throw new Exception(
                    'Appointments can only be booked for tomorrow.'
                );
            }


            $appointmentStart = Carbon::parse(
                $appointmentDate . ' ' . $requestedStart->format('H:i:s')
            );

            if ($appointmentStart->lte(now())) {
                throw new Exception(
                    'You cannot book an appointment that has already started or passed.'
                );
            }


            $studentHasOverlappingAppointment = CounselorAppointment::query()
                ->where('student_id', $studentId)
                ->whereDate('appointment_date', $appointmentDate)
                ->whereIn('booking_status', [
                    'pending',
                    'accepted',
                ])
                ->where('start_time', '<', $requestedEnd->format('H:i:s'))
                ->where('end_time', '>', $requestedStart->format('H:i:s'))
                ->exists();

            if ($studentHasOverlappingAppointment) {
                throw new Exception(
                    'You already have another appointment that overlaps with this time.'
                );
            }


            $appointments = CounselorAppointment::query()
                ->whereDate('appointment_date', $appointmentDate)
                ->where('start_time', $requestedStart->format('H:i:s'))
                ->where('end_time', $requestedEnd->format('H:i:s'))
                ->where('booking_status', 'available')
                ->where('slot_status', 'available')
                ->lockForUpdate()
                ->get();

            if ($appointments->isEmpty()) {
                throw new Exception(
                    'This appointment slot is no longer available.'
                );
            }

            $day = strtolower(
                Carbon::parse($appointmentDate)->englishDayOfWeek
            );

            $counselorIds = $appointments
                ->pluck('counselor_id')
                ->unique()
                ->values();

            $availabilities = CounselorAvailability::query()
                ->whereIn('counselor_id', $counselorIds)
                ->where('day', $day)
                ->where('is_active', true)
                ->get()
                ->keyBy('counselor_id');



            $acceptedCounts = CounselorAppointment::query()
                ->selectRaw('counselor_id, COUNT(*) as count')
                ->whereIn('counselor_id', $counselorIds)
                ->whereDate('appointment_date', $appointmentDate)
                ->where('booking_status', 'accepted')
                ->groupBy('counselor_id')
                ->pluck('count', 'counselor_id');

            $selectedAppointment = null;
            $lowestLoad = PHP_INT_MAX;

            foreach ($appointments as $appointment) {

                $availability = $availabilities->get(
                    $appointment->counselor_id
                );

                if (!$availability) {
                    continue;
                }

                $acceptedCount = (int) $acceptedCounts->get(
                    $appointment->counselor_id,
                    0
                );

                $dailyLimit = (int) $availability->daily_sessions_limit;

                if ($acceptedCount >= $dailyLimit) {
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
                'counselor.user',
            ]);

            if (!$selectedAppointment->student) {
                throw new Exception(
                    'The student could not be loaded for this appointment.'
                );
            }

            if (!$selectedAppointment->student->user) {
                throw new Exception(
                    'The student user account could not be loaded.'
                );
            }

            $studentName = trim(
                $selectedAppointment->student->user->first_name
                . ' '
                . $selectedAppointment->student->user->last_name
            );

            if ($selectedAppointment->counselor) {
                $this->alertService->createStaffAlert(
                    $selectedAppointment->counselor,
                    Alert::TYPE_COUNSELING_REQUEST,
                    'New counseling appointment request',
                    'A new counseling appointment request has been submitted. Please review the appointment requests.',
                    [
                        'student_name' => $studentName,
                        'appointment_date' => $selectedAppointment
                            ->appointment_date
                            ->toDateString(),
                        'start_time' => $selectedAppointment->start_time,
                        'end_time' => $selectedAppointment->end_time,
                        'appointment_id' => $selectedAppointment->id,
                    ]
                );
            }

            return $selectedAppointment;
        });
    }

    public function approveAppointment(int $counselorId, int $appointmentId)
    {
        return DB::transaction(function () use ($counselorId, $appointmentId) {

            // 1. جلب الموعد والتأكد من أنه معلق ويخص هذا المرشد
            $appointment = CounselorAppointment::query()
                ->where('id', $appointmentId)
                ->where('counselor_id', $counselorId)
                ->where('booking_status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$appointment) {
                throw new Exception('الموعد غير موجود أو أنه ليس في حالة قيد الانتظار.');
            }

            // نستخرج التاريخ واليوم مباشرة من الموعد بدلاً من الاعتماد على مدخلات المستخدم
            $date = Carbon::parse($appointment->appointment_date)->toDateString();
            $day = strtolower(Carbon::parse($appointment->appointment_date)->englishDayOfWeek);

            // 2. التحقق من جدول توفر المرشد في هذا اليوم
            $availability = CounselorAvailability::query()
                ->where('counselor_id', $counselorId)
                ->where('day', $day)
                ->where('is_active', true)
                ->first();

            if (!$availability) {
                throw new Exception('لا يوجد جدول توفر نشط لهذا اليوم.');
            }

            // 3. حساب عدد المواعيد المقبولة في هذا اليوم للتحقق من الحد الأقصى
            $acceptedCount = CounselorAppointment::query()
                ->where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $date)
                ->where('booking_status', 'accepted')
                ->lockForUpdate()
                ->count();

            $dailyLimit = (int) $availability->daily_sessions_limit;

            if ($acceptedCount >= $dailyLimit) {
                throw new Exception('تم الوصول إلى الحد الأقصى للجلسات الإرشادية اليومية.');
            }

            // 4. تحديث حالة الموعد إلى مقبول
            $appointment->update([
                'booking_status' => 'accepted',
                'slot_status' => 'booked',
            ]);

            // 5. إرسال الإشعار للطالب
            $academicSetting = \App\Models\AcademicSetting::query()->first();

            if (!$academicSetting || !$academicSetting->current_academic_year_id) {
                throw new Exception('إعدادات السنة الأكاديمية غير مكتملة.');
            }

            $appointment->load(['student.user']);

            if ($appointment->student) {
                $enrollment = $appointment->student
                    ->enrollments()
                    ->where('academic_year_id', $academicSetting->current_academic_year_id)
                    ->where('enrollment_status', 'enrolled')
                    ->first();

                if ($enrollment) {
                    $this->alertService->createStudentOnlyAlert(
                        $enrollment,
                        Alert::TYPE_COUNSELING_RESPONSE,
                        'قبول طلب موعد',
                        'قام المرشد النفسي بقبول طلب حجز الموعد الذي قمت به مسبقا.',
                        [
                            'status' => 'accepted',
                            'appointment_id' => $appointment->id,
                            'appointment_date' => $date,
                            'start_time' => $appointment->start_time,
                            'end_time' => $appointment->end_time,
                        ]
                    );
                }
            }

            return $appointment;
        });
    }
    public function getPendingCounselorAppointments(int $counselorId, ?string $date = null)
    {

        $date = $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::tomorrow()->toDateString();

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
            ->orderBy('start_time')
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
                ->where(
                    'counselor_id',
                    $counselorId
                )
                ->lockForUpdate()
                ->first();

            if (!$appointment) {
                throw new Exception(
                    'Appointment does not existe or you do not have the permission to cancel it.'
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
        return CounselorAppointment::query()

            ->where(
                'counselor_id',
                $counselorId
            )

            ->whereDate(
                'appointment_date',
                Carbon::tomorrow()->toDateString()
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
        // إذا لم يتم تمرير تاريخ، نجلب مواعيد الغد كافتراضي (نفس منطقك السابق)
        $date = $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::tomorrow()->toDateString();

        return CounselorAppointment::query()
            ->where('counselor_id', $counselorId)
            ->whereDate('appointment_date', $date)
            ->where('booking_status', 'accepted')
            ->with([
                'student.user',
            ])
            ->orderBy('start_time')
            ->get();
    }





}
