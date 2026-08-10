<?php

namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Staff\StoreStaffAttendanceRequest;
use App\Http\Requests\Admin\Staff\UpdateStaffAttendanceRequest;
use App\Services\Staff\StaffAttendanceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ApiResource; // الـ Trait الخاص بك
use Illuminate\Http\JsonResponse;
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
            return $this->successResponse(new StaffAttendanceResource($attendance), 'تم إنشاء سجل الحضور/الغياب بنجاح.', 201);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الدوام.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateStaffAttendanceRequest $request, int $id): JsonResponse
    {
        try {
            $attendance = $this->service->updateAttendance($id, $request->validated());
            return $this->successResponse(new StaffAttendanceResource($attendance), 'تم تعديل سجل الدوام وتحديث الحصص بنجاح.');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل السجل.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $attendance = $this->service->getAttendanceById($id);
            return $this->successResponse(new StaffAttendanceResource($attendance), 'تم جلب سجل الدوام بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الحضور غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب سجل الحضور.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteAttendance($id);
            return $this->successResponse(null, 'تم حذف السجل بالكامل بنجاح.');
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء الحذف.', 500, ['error' => $e->getMessage()]);
        }
    }
}
