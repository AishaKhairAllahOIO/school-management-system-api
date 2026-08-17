<?php

namespace App\Http\Controllers\Counselor;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\Counselor\CounselorAppointmentService;
use Exception;
use Illuminate\Http\Request;

class CounselorAppointmentController extends Controller
{
    use ApiResource;

    public function __construct(private CounselorAppointmentService $service)
    {
    }

    public function cancel(Request $request, int $appointmentId)
    {
        try {
            $counselorId = $request->user()->id;

            $this->service->cancelByCounselor($appointmentId, $counselorId);

            return $this->successResponse(
                null,
                'Counseling appointment cancelled successfully.',
                200
            );
        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'لا يمكن') ? 403 : 422;
            return $this->errorResponse(
                $e->getMessage(),
                $statusCode
            );
        }
    }
}
