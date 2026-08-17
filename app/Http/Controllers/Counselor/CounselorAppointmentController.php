<?php

namespace App\Http\Controllers\Counselor;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Counselor\CounselorAppointmentManagementResource;
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

    public function cancel(Request $request, int $appointmentId)
    {
        try {

            $counselorId = $request->user()->id;

            $appointment = $this->service->cancelByCounselor(
                $appointmentId,
                $counselorId
            );

            return $this->successResponse(
                null,
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
}