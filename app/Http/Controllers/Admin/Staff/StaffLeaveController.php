<?php

namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Staff\StoreStaffLeaveRequest;
use App\Http\Requests\Admin\Staff\UpdateStaffLeaveRequest;
use App\Services\Staff\StaffLeaveService;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use App\Http\Resources\Staff\StaffLeaveResource;
use App\Http\Requests\Admin\Staff\UpadateStaffLeaveRequest;
use App\Models\StaffLeave;
use Illuminate\Http\Request;

class StaffLeaveController extends Controller
{
    use ApiResource;

    protected StaffLeaveService $service;

    public function __construct(StaffLeaveService $service)
    {
        $this->service = $service;
    }

    /**
     * تسجيل طلب إجازة جديد للموظف
     */
 public function getAllRecords()
    {
        return StaffLeave::get();
    }
    public function store(StoreStaffLeaveRequest $request): JsonResponse
    {
        try {
            $leave = $this->service->createLeave($request->validated());
            return $this->successResponse(new StaffLeaveResource($leave->load('leaveType')), 'Leave request submitted successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * تعديل سجل إجازة
     */
    public function update(UpadateStaffLeaveRequest $request, int $id): JsonResponse
    {
        try {
            $leave = $this->service->updateLeave($id, $request->validated());
            return $this->successResponse(new StaffLeaveResource($leave->load('leaveType')), 'Leave updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Leave not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * حذف سجل إجازة
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteLeave($id);
            return $this->successResponse(null, 'Leave deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Leave not found', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض جميع إجازات موظف معين (الدالة الجديدة المطلوبة)
     */
    public function getStaffLeaves(int $staffId): JsonResponse
    {
        try {
            $leaves = $this->service->getStaffLeaves($staffId);
            return $this->successResponse(StaffLeaveResource::collection($leaves), 'Staff leaves retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getMyLeaves(Request $request): JsonResponse
    {
        try {
            $staffId = $request->user()->staff->id;
            $leaves = $this->service->getStaffLeaves($staffId);
            return $this->successResponse(StaffLeaveResource::collection($leaves), 'My leaves retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function getStaffLeaveById(int $id): JsonResponse
    {
        try {
            $leave = $this->service->getStaffLeaveById($id);
            return $this->successResponse(new StaffLeaveResource($leave), 'Leave details retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Leave not found', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
