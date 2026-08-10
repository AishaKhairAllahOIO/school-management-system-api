<?php

namespace App\Http\Controllers\Admin\Leave;

use App\Http\Controllers\Controller;

use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Throwable;
use App\Services\Leave\StaffLeaveTypeService;
use App\Http\Resources\Leave\StaffLeaveTypeResource;
use App\Http\Requests\Admin\Leave\StoreStaffLeaveTypeRequest;
use App\Http\Requests\Admin\Leave\UpdateStaffLeaveTypeRequest;


class StaffLeaveTypeController extends Controller
{
    use ApiResource;

    protected StaffLeaveTypeService $service;

    public function __construct(StaffLeaveTypeService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $types = $this->service->getAllLeaveTypes();
            return $this->successResponse(StaffLeaveTypeResource::collection($types), 'تم جلب أنواع الإجازات بنجاح.');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب أنواع الإجازات.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function show(int $id)
    {
        try{
            
            $leaveType = $this->service->getLeaveById($id);
            return $this->successResponse(new StaffLeaveTypeResource($leaveType), 'تم جلب نوع الإجازة بنجاح.');
         }catch (ModelNotFoundException $e) {
            return $this->errorResponse('نوع الإجازة غير موجود.', 404);
         }
    }

    public function store(StoreStaffLeaveTypeRequest $request): JsonResponse
    {
        try {
            $type = $this->service->createLeaveType($request->validated());
            return $this->successResponse(new StaffLeaveTypeResource($type), 'تم إنشاء نوع الإجازة بنجاح.', 201);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء إنشاء نوع الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateStaffLeaveTypeRequest $request, int $id): JsonResponse
    {
        try {
            $type = $this->service->updateLeaveType($id, $request->validated());
            return $this->successResponse(new StaffLeaveTypeResource($type), 'تم تعديل نوع الإجازة بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('نوع الإجازة غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل نوع الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteLeaveType($id);
            return $this->successResponse(null, 'تم حذف نوع الإجازة بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('نوع الإجازة غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400,['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف نوع الإجازة.', 500, ['error' => $e->getMessage()]);
        }
    }
}