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
            return $this->successResponse(new StaffLeaveResource($leave->load('leaveType')), 'تم تسجيل إجازة الموظف بنجاح.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * تعديل سجل إجازة
     */
    public function update(UpadateStaffLeaveRequest $request, int $id): JsonResponse
    {
        try {
            $leave = $this->service->updateLeave($id, $request->validated());
            return $this->successResponse(new StaffLeaveResource($leave->load('leaveType')), 'تم تعديل إجازة الموظف بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الإجازة غير موجود.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * حذف سجل إجازة
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteLeave($id);
            return $this->successResponse(null, 'تم حذف إجازة الموظف وإعادة تفعيل دوامه بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الإجازة غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض جميع إجازات موظف معين (الدالة الجديدة المطلوبة)
     */
    public function getStaffLeaves(int $staffId): JsonResponse
    {
        try {
            $leaves = $this->service->getStaffLeaves($staffId);
            return $this->successResponse(StaffLeaveResource::collection($leaves), 'تم جلب سجل إجازات الموظف بنجاح.');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب سجل إجازات الموظف.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getMyLeaves(Request $request): JsonResponse
    {
        try {
            $staffId = $request->user()->staff->id;
            $leaves = $this->service->getStaffLeaves($staffId);
            return $this->successResponse(StaffLeaveResource::collection($leaves), 'تم جلب سجل إجازاتي بنجاح.');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب سجل إجازات الموظف.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function getStaffLeaveById(int $id): JsonResponse
    {
        try {
            $leave = $this->service->getStaffLeaveById($id);
            return $this->successResponse(new StaffLeaveResource($leave), 'تم جلب سجل الإجازة بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الإجازة غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب سجل الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }
}
