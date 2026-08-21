<?php

namespace App\Http\Controllers\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\BookCounselorAppointmentRequest;
use App\Http\Resources\Counselor\CounselorAppointmentResource;
use App\Services\Counselor\CounselorAppointmentService;
use Exception;
use Illuminate\Http\Request;

class StudentCounselorAppointmentController extends Controller
{
    use ApiResource;

    public function __construct(private CounselorAppointmentService $service)
    {
    }

    public function availableSlots()
    {
        try {

            $appointments = $this->service->getAvailableTomorrowSlots();

            return $this->successResponse(
                CounselorAppointmentResource::collection($appointments),
                'Available counseling times retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function store(BookCounselorAppointmentRequest $request)
    {
        try {
            $studentId = $request->user()->student->id;

            $appointment = $this->service->bookAppointment(
                $studentId,
                $request->appointment_date,
                $request->start_time,
                $request->end_time
            );

            return $this->successResponse(
                new CounselorAppointmentResource($appointment),
                'Counseling appointment request submitted successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

    public function cancel(Request $request, int $appointmentId)
    {
        try {
            $studentId = $request->user()->student->id;

            $this->service->cancelByStudent($appointmentId, $studentId);

            return $this->successResponse(
                null,
                'Counseling appointment cancelled successfully.',
                200
            );
        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'غير موجود') ? 404 : 403;
            return $this->errorResponse(
                $e->getMessage(),
                $statusCode
            );
        }
    }


}
