<?php

namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Staff\StoreStaffAttendanceRequest;
use App\Http\Requests\Admin\Staff\UpdateStaffAttendanceRequest;
use App\Services\Staff\StaffAttendanceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ApiResource; // الـ Trait الخاص بك
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use App\Http\Resources\Staff\StaffAttendanceResource;

class StaffAttendanceController extends Controller
{
    use ApiResource;

    protected StaffAttendanceService $service;

    public function __construct(StaffAttendanceService $service)
    {
        $this->service = $service;
    }

    public function store(StoreStaffAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->service->storeAttendance($request->validated());
            return $this->successResponse(new StaffAttendanceResource($attendance), 'Staff attendance created successfully.', 201);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateStaffAttendanceRequest $request, int $id): JsonResponse
    {
        try {
            $attendance = $this->service->updateAttendance($id, $request->validated());
            return $this->successResponse(new StaffAttendanceResource($attendance), 'Staff attendance updated successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $attendance = $this->service->getAttendanceById($id);
            return $this->successResponse($attendance, 'Staff attendance fetched successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff attendance record not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteAttendance($id);
            return $this->successResponse(null, 'Staff attendance record deleted successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function getAllRecords(int $staffId, Request $request): JsonResponse
    {
        try {
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $records = $this->service->getStaffAttendance($staffId, $fromDate, $toDate);
            return $this->successResponse($records, 'Staff attendance records fetched successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
