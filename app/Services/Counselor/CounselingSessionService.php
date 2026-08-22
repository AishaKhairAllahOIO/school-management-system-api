<?php

namespace App\Services\Counselor;

use App\Models\Alert;
use App\Models\CounselingSession;
use App\Services\User\AlertService;
use Exception;
use Illuminate\Support\Facades\DB;

class CounselingSessionService
{
    public function __construct(
        private AlertService $alertService
    ) {
    }

    public function getPendingSessions(int $counselorId)
    {
        return CounselingSession::query()

            ->whereHas('appointment', function ($query) use ($counselorId) {

                $query->where(
                    'counselor_id',
                    $counselorId
                );

            })

            ->where(
                'attendance_status',
                'not_marked'
            )

            ->with([
                'appointment.student.user',
            ])

            ->orderByDesc(
                'created_at'
            )

            ->get();
    }

    public function updateSession(int $sessionId, int $counselorId, array $data): CounselingSession
    {

        return DB::transaction(function () use ($sessionId, $counselorId, $data) {

            $session = CounselingSession::query()
                ->where('id', $sessionId)
                ->whereHas('appointment', function ($query) use ($counselorId) {
                    $query->where('counselor_id', $counselorId);
                })
                ->with([
                    'appointment.student',
                ])
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw new Exception(
                    'الجلسة غير موجودة أو لا تملك صلاحية تعديلها.'
                );
            }

            if (
                $session->appointment->booking_status !== 'completed'
            ) {
                throw new Exception(
                    'لا يمكن تسجيل نتيجة الجلسة قبل انتهائها.'
                );
            }

            if (
                $session->attendance_status !== 'not_marked'
            ) {
                throw new Exception(
                    'تم تسجيل نتيجة هذه الجلسة مسبقاً.'
                );
            }

            if (
                $data['attendance_status'] === 'absent'
                && !empty($data['assessment'])
            ) {
                throw new Exception(
                    'لا يمكن تقييم الجلسة إذا لم يحضر الطالب.'
                );
            }

            $session->update([
                'attendance_status' => $data['attendance_status'],

                'assessment' =>
                    $data['attendance_status'] === 'present'
                    ? ($data['assessment'] ?? null)
                    : null,

                'notes' => $data['notes'] ?? null,
            ]);

            $session->refresh();


            if (
                $session->attendance_status === 'present'
                && $session->assessment === 'critical'
            ) {

                $student = $session->appointment->student;

                if ($student) {

                    $enrollment = $student
                        ->enrollments()
                        ->latest()
                        ->first();

                    if ($enrollment) {

                        $this->alertService->createGuardianAlert(
                            $enrollment,
                            Alert::TYPE_WARNING,
                            'إشعار إرشادي هام',
                            'يرجى مراجعة إدارة المدرسة بخصوص الجلسة الإرشادية الأخيرة للطالب للحصول على تفاصيل هامة.',
                            [
                                'appointment_id' =>
                                    $session->appointment_id,

                                'appointment_date' =>
                                    $session
                                        ->appointment
                                        ->appointment_date
                                        ->toDateString(),

                                'status' => 'critical',
                            ]
                        );
                    }
                }
            }

            return $session->load([
                'appointment.student.user',
            ]);
        });
    }
}
