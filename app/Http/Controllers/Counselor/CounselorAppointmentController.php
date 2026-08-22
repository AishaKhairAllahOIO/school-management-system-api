<?php

namespace App\Http\Controllers\Counselor;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Counselor\ApproveCounselorAppointmentsRequest;
use App\Http\Requests\Counselor\GetCounselorAppointmentsRequest;
use App\Http\Resources\Counselor\CounselorAppointmentManagementResource;
use App\Http\Resources\Counselor\CounselorStudentResource;
use App\Services\Counselor\CounselorAppointmentService;
use Exception;
use Illuminate\Http\Request;

class CounselorAppointmentController extends Controller
{
    use ApiResource;

    public function __construct(
        private CounselorAppointmentService $service
    ) {
    }

    public function pending(GetCounselorAppointmentsRequest $request)
    {
        try {

            $counselorId = $request->user()->id;

            $appointments = $this->service->getPendingCounselorAppointments(
                $counselorId,
                $request->validated('date')
            );

            return $this->successResponse(
                CounselorAppointmentManagementResource::collection($appointments),
                'Pending counseling requests retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Error:Server',
                500
            );
        }
    }
    public function approve(Request $request, int $appointmentId)
    {
        try {
            $counselorId = $request->user()->id;

            $appointment = $this->service->approveAppointment(
                $counselorId,
                $appointmentId
            );

            return $this->successResponse(
                new CounselorAppointmentManagementResource($appointment),
                'Counseling appointment approved successfully.',
                200
            );

        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'غير موجود') ? 404 : 422;

            return $this->errorResponse(
                $e->getMessage(),
                $statusCode
            );
        }
    }
    public function cancel(Request $request, int $appointmentId)
    {
        try {

            $appointment = $this->service->cancelByCounselor(
                $appointmentId,
                $request->user()->id
            );

            return $this->successResponse(
                new CounselorAppointmentManagementResource($appointment),
                'Counseling appointment cancelled successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

    public function myAppointments(Request $request)
    {
        try {

            $studentId = $request->user()->student->id;
            $appointments = $this->service->getStudentAppointments($studentId);
            return $this->successResponse(
                CounselorAppointmentManagementResource::collection(
                    $appointments
                ),
                'Student counseling appointments retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function students(Request $request)
    {
        try {

            $students = $this->service->getCounselorStudents(
                $request->user()->id,
                $request->grade_level_id,
                $request->search
            );


            return $this->successResponse(
                CounselorStudentResource::collection($students),
                'Counselor students retrieved successfully.'
            );


        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function sessions(Request $request, int $studentId)
    {

        try {


            $sessions =
                $this->service->getStudentSessions(
                    $studentId,
                    $request->user()->id
                );


            return $this->successResponse(
                $sessions,
                'Student counseling sessions retrieved successfully'
            );


        } catch (Exception $e) {


            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function tomorrowSchedule(Request $request)
    {
        try {

            $appointments =
                $this->service->getTomorrowSchedule(
                    $request->user()->id
                );


            return $this->successResponse(
                CounselorAppointmentManagementResource::collection($appointments),
                'Tomorrow counseling schedule retrieved successfully.',
                200
            );


        } catch (Exception $e) {


            return $this->errorResponse(
                $e->getMessage(),
                500
            );

        }
    }

    public function accepted(GetCounselorAppointmentsRequest $request)
    {
        try {
            $counselorId = $request->user()->id;

            $appointments = $this->service->getAcceptedCounselorAppointments(
                $counselorId,
                $request->validated('date')
            );

            return $this->successResponse(
                CounselorAppointmentManagementResource::collection($appointments),
                'Accepted counseling appointments retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

    public function completeExpiredAppointments(
    CounselorAppointmentService $service
) {
    try {

        $count = $service->completeExpiredAppointments();

        return $this->successResponse(
            [
                'completed_count' => $count
            ],
            'Expired appointments completed successfully.',
            200
        );

    } catch (\Exception $e) {

        return $this->errorResponse(
            $e->getMessage(),
            500
        );
    }
}

}
