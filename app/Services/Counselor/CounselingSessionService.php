<?php

namespace App\Services\Counselor;

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
                $data['attendance_status'] === 'absent'
                && !empty($data['assessment'])
            ) {
                throw new Exception(
                    'لا يمكن تقييم الجلسة إذا لم يحضر الطالب.'
                );
            }

            $session->update([
                'attendance_status' =>
                    $data['attendance_status'],

                'assessment' =>
                    $data['attendance_status'] === 'present'
                    ? ($data['assessment'] ?? null)
                    : null,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            $session->refresh();

            /*
            |--------------------------------------------------------------------------
            | إرسال إشعار لولي الأمر عند الحالة الحرجة
            |--------------------------------------------------------------------------
            */

            if (
                $session->attendance_status === 'present'
                && $session->assessment === 'critical'
            ) {

                $student = $session->appointment->student;

                if ($student && $student->guardian) {

                    // هنا نستخدم AlertService الخاص بك
                    // ونرسل رسالة عامة دون تفاصيل حساسة.

                    // مثال:
                    //
                    // $this->alertService->createGuardianAlert(
                    //     $student->guardian,
                    //     ...
                    // );
                }
            }

            return $session->load([
                'appointment.student.user',
            ]);
        });
    }
}
