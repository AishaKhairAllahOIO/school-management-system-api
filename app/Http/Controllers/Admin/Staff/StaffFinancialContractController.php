<?php

namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Staff\StoreStaffFinancialContractRequest;
use App\Http\Requests\Admin\Staff\UpdateStaffFinancialContractRequest;
use App\Http\Resources\Staff\StaffFinancialContractResource;
use App\Services\Staff\StaffFinancialContractService;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class StaffFinancialContractController extends Controller
{
    use ApiResource;

    protected StaffFinancialContractService $service;

    public function __construct(StaffFinancialContractService $service)
    {
        $this->service = $service;
    }

    /**
     * عرض جميع العقود المالية مع إمكانية الفلترة
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $contracts = $this->service->getAllContracts($request->all());
            return $this->successResponse(
                StaffFinancialContractResource::collection($contracts),
                'تم جلب العقود المالية للموظفين بنجاح.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب العقود المالية.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * إنشاء عقد مالي جديد
     */
    public function store(StoreStaffFinancialContractRequest $request): JsonResponse
    {
        try {
            $contract = $this->service->createContract($request->validated());
            return $this->successResponse(
                new StaffFinancialContractResource($contract->load(['staff.user', 'academicYear'])),
                'تم إنشاء العقد المالي بنجاح.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء إنشاء العقد المالي.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض تفاصيل عقد مالي محدد
     */
    public function show(int $id): JsonResponse
    {
        try {
            $contract = $this->service->getContractById($id);
            return $this->successResponse(
                new StaffFinancialContractResource($contract),
                'تم جلب تفاصيل العقد بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('العقد المالي غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب العقد.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * تعديل عقد مالي
     */
    public function update(UpdateStaffFinancialContractRequest $request, int $id): JsonResponse
    {
        try {
            $contract = $this->service->updateContract($id, $request->validated());
            return $this->successResponse(
                new StaffFinancialContractResource($contract),
                'تم تعديل العقد المالي بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('العقد المالي غير موجود.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل العقد.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * حذف عقد مالي
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteContract($id);
            return $this->successResponse(null, 'تم حذف العقد المالي بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('العقد المالي غير موجود.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف العقد.', 500, ['error' => $e->getMessage()]);
        }
    }
}