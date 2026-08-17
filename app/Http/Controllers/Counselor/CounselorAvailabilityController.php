<?php

namespace App\Http\Controllers\Counselor;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Counselor\StoreAvailabilityRequest;
use App\Http\Requests\Counselor\UpdateAvailabilityRequest;
use App\Services\Counselor\CounselorAvailabilityService;
use Exception;
use Illuminate\Http\Request;

class CounselorAvailabilityController extends Controller
{
    use ApiResource;

    public function __construct(private CounselorAvailabilityService $service)
    {
    }

    public function store(StoreAvailabilityRequest $request)
    {
        try {
            $counselorId = $request->user()->id;
            $this->service->saveSchedule($counselorId, $request->schedule);

            return $this->successResponse(null, 'Available times saved successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function index(Request $request)
    {
        try {
            $data = $this->service->getSchedule($request->user()->id);

            return $this->successResponse(
                $data,
                'Available times shown successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function update(UpdateAvailabilityRequest $request, string $day)
    {
        try {
            $counselorId = $request->user()->id;
            $availability = $this->service->updateDay($counselorId, $day, $request->validated());

            return $this->successResponse(
                $availability,
                'Available times updated successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function destroy(Request $request, string $day)
    {
        try {
            $this->service->deleteDay($request->user()->id, $day);

            return $this->successResponse(null, 'Day deleted successfully.', 200);
        } catch (Exception $e) {
            // تحسين الردود بحسب نوع الخطأ القادم من السيرفس (مثلاً لو كان هناك حجوزات نشطة)
            $statusCode = str_contains($e->getMessage(), 'حجوزات') ? 422 : 500;
            return $this->errorResponse(
                $e->getMessage(),
                $statusCode
            );
        }
    }
}
