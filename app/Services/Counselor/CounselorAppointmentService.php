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
        return DB::transaction(function () {
            $tomorrow = Carbon::tomorrow();
            $day = strtolower($tomorrow->englishDayOfWeek);

            $availabilities = CounselorAvailability::where('day', $day)
                ->where('is_active', true)
                ->get();

            $insertData = [];
            $createdCount = 0;

            foreach ($availabilities as $availability) {
                $start = Carbon::createFromFormat('H:i:s', $availability->start_time);
                $end = Carbon::createFromFormat('H:i:s', $availability->end_time);
                $duration = (int) $availability->session_duration;

                while ($start->copy()->addMinutes($duration)->lte($end)) {
                    $slotStart = $start->format('H:i:s');
                    $slotEnd = $start->copy()->addMinutes($duration)->format('H:i:s');

                    // استخدام upsert أو insertIgnore هنا أفضل بكثير،
                    // ولكن لتجنب تعقيد المفاتيح المركبة، سنقوم بتجهيز مصفوفة
                    $insertData[] = [
                        'counselor_id' => $availability->counselor_id,
                        'student_id' => null,
                        'appointment_date' => $tomorrow->toDateString(),
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'booking_status' => 'available',
                        'slot_status' => 'available',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $start->addMinutes($duration);
                    $createdCount++;
                }
            }

            // تنفيذ الإدراج إذا كان الموعد غير موجود مسبقاً بناءً على مفتاح فريد (تأكدي من وجود Unique Index في الداتابيز)
            // أو استخدام insertOrIgnore
            if (!empty($insertData)) {
                CounselorAppointment::insertOrIgnore($insertData);
            }

            return $createdCount;
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

            $studentAlreadyBooked = CounselorAppointment::where('student_id', $studentId)
                ->whereDate('appointment_date', $appointmentDate)
                ->where('start_time', $startTime)
                ->whereIn('booking_status', ['pending', 'accepted'])
                ->exists();

            if ($studentAlreadyBooked) {
                throw new Exception('لديك بالفعل موعد في هذا الوقت.');
            }

            $appointments = CounselorAppointment::whereDate('appointment_date', $appointmentDate)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->where('booking_status', 'available')
                ->lockForUpdate()
                ->get();

            if ($appointments->isEmpty()) {
                throw new Exception('هذا الموعد لم يعد متاحاً.');
            }

            $selectedAppointment = null;
            $lowestLoad = PHP_INT_MAX;
            $day = strtolower(Carbon::parse($appointmentDate)->englishDayOfWeek);

            // جلب التوافر لكل المرشدين مسبقاً توفيراً للاستعلامات
            $counselorIds = $appointments->pluck('counselor_id')->toArray();
            $availabilities = CounselorAvailability::whereIn('counselor_id', $counselorIds)
                ->where('day', $day)->where('is_active', true)->get()->keyBy('counselor_id');

            // جلب عدد الجلسات المقبولة مسبقاً دفعة واحدة
            $acceptedCounts = CounselorAppointment::selectRaw('counselor_id, count(*) as count')
                ->whereIn('counselor_id', $counselorIds)
                ->whereDate('appointment_date', $appointmentDate)
                ->where('booking_status', 'accepted')
                ->groupBy('counselor_id')
                ->pluck('count', 'counselor_id');

            foreach ($appointments as $appointment) {
                $availability = $availabilities->get($appointment->counselor_id);

                if (!$availability)
                    continue;

                $acceptedCount = $acceptedCounts->get($appointment->counselor_id, 0);

                if ($acceptedCount >= $availability->daily_sessions_limit)
                    continue;

                if ($acceptedCount < $lowestLoad) {
                    $lowestLoad = $acceptedCount;
                    $selectedAppointment = $appointment;
                }
            }

            if (!$selectedAppointment) {
                throw new Exception('لا يوجد مرشد متاح لهذا الموعد حالياً.');
            }

            $selectedAppointment->update([
                'student_id' => $studentId,
                'booking_status' => 'pending',
                'slot_status' => 'booked',
            ]);

            $selectedAppointment->load(['student.user', 'counselor.user']);

            // تحسين: الاستغناء عن findOrFail لأننا عملنا load بالفعل
            $studentName = $selectedAppointment->student->user->first_name . ' ' . $selectedAppointment->student->user->last_name;

            if ($selectedAppointment->counselor) {
                $this->alertService->createStaffAlert(
                    $selectedAppointment->counselor,
                    Alert::TYPE_COUNSELING_REQUEST,
                    'طلب جلسة إرشاد جديدة',
                    'تم إرسال طلب جديد لحجز جلسة إرشاد. يرجى مراجعة طلبات المواعيد.',
                    [
                        'student_name' => $studentName,
                        'appointment_date' => $selectedAppointment->appointment_date->toDateString(),
                    ]
                );
            }

            return $selectedAppointment;
        });
    }

    public function approveAppointments(int $counselorId, array $appointmentIds, string $date)
    {
        return DB::transaction(function () use ($counselorId, $appointmentIds, $date) {

            $date = Carbon::parse($date)->toDateString();
            $day = strtolower(Carbon::parse($date)->englishDayOfWeek);

            $availability = CounselorAvailability::where('counselor_id', $counselorId)
                ->where('day', $day)
                ->where('is_active', true)
                ->first();

            if (!$availability) {
                throw new Exception('لا يوجد جدول تواجد للمرشد في هذا اليوم.');
            }

            $pendingAppointments = CounselorAppointment::where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $date)
                ->where('booking_status', 'pending')
                ->lockForUpdate()
                ->get();

            $selectedAppointments = $pendingAppointments->whereIn('id', $appointmentIds)->values();

            if ($selectedAppointments->count() !== count($appointmentIds)) {
                throw new Exception('بعض المواعيد المحددة لم تعد متاحة للاعتماد.');
            }

            $acceptedCount = CounselorAppointment::where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $date)
                ->where('booking_status', 'accepted')
                ->lockForUpdate()
                ->count();

            $dailyLimit = (int) $availability->daily_sessions_limit;
            $remainingCapacity = $dailyLimit - $acceptedCount;

            if ($selectedAppointments->count() > $remainingCapacity) {
                throw new Exception("لا يمكنك قبول أكثر من {$remainingCapacity} جلسات إضافية لهذا اليوم.");
            }

            // تغيير حالة المواعيد المحددة إلى مقبولة
            foreach ($selectedAppointments as $appointment) {
                $appointment->update(['booking_status' => 'accepted']);
            }

            // ما تبقى من الطلبات المرفوضة في هذا اليوم يصبح غير متاح
            CounselorAppointment::where('counselor_id', $counselorId)
                ->whereDate('appointment_date', $date)
                ->where('booking_status', 'pending')
                ->update(['booking_status' => 'not_available']);

            // جلب المواعيد التي تم معالجتها (المقبولة والمرفوضة) لإرسال الإشعارات
            $processedIds = $pendingAppointments->pluck('id');
            $processedAppointments = CounselorAppointment::whereIn('id', $processedIds)
                ->with(['student.user'])
                ->get();

            foreach ($processedAppointments as $appointment) {
                if (!$appointment->student)
                    continue;

                $enrollment = $appointment->student->enrollments()->latest()->first();
                if (!$enrollment)
                    continue;

                if ($appointment->booking_status === 'accepted') {
                    $this->alertService->createStudentOnlyAlert(
                        $enrollment,
                        Alert::TYPE_COUNSELING_RESPONSE,
                        'تم قبول موعد الإرشاد',
                        'تم قبول طلب جلسة الإرشاد الخاصة بك.',
                        [
                            'status' => 'accepted',
                            'appointment_id' => $appointment->id,
                            'appointment_date' => $appointment->appointment_date->toDateString(),
                            'start_time' => $appointment->start_time,
                            'end_time' => $appointment->end_time,
                        ]
                    );
                } elseif ($appointment->booking_status === 'not_available') {
                    $this->alertService->createStudentOnlyAlert(
                        $enrollment,
                        Alert::TYPE_COUNSELING_RESPONSE,
                        'موعد الإرشاد غير متاح',
                        'نعتذر، لم يعد الموعد الذي طلبته متاحاً.',
                        [
                            'status' => 'not_available',
                            'appointment_id' => $appointment->id,
                            'appointment_date' => $appointment->appointment_date->toDateString(),
                            'start_time' => $appointment->start_time,
                            'end_time' => $appointment->end_time,
                        ]
                    );
                }
            }

            return $processedAppointments;
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
                'booking_status' => 'cancelled',
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
                'booking_status' => 'cancelled',
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






}
