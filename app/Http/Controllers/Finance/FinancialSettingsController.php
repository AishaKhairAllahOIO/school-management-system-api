<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinancialSettingsService;
use App\Http\Requests\Finance\InstallmentPolicyRequest;
use App\Http\Requests\Finance\FeePlanRequest;
use App\Http\Requests\Finance\UpdatePolicyItemRequest;
use App\Http\Requests\Finance\UpdateExtraServiceRequest;
use App\Http\Resources\Finance\InstallmentPolicyResource;
use App\Http\Resources\Finance\FeePlanResource;
use App\Http\Resources\Finance\InstallmentPolicyItemResource;
use App\Http\Resources\Finance\FeePlanExtraServiceResource;
use App\ApiResource; 
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class FinancialSettingsController extends Controller
{
    use ApiResource;

    public function __construct(private FinancialSettingsService $service) {}

    // =========================================================
    // ---- واجهات سياسات التقسيط ----
    // =========================================================

    public function getPolicies(): JsonResponse
    {
        return $this->successResponse(
            InstallmentPolicyResource::collection($this->service->getPolicies()),
            'تم جلب سياسات التقسيط بنجاح.'
        );
    }

    // 👈 إضافة دالة جلب سياسة محددة (Show)
    public function showPolicy(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyResource($this->service->getPolicyById($id)),
                'تم جلب بيانات سياسة التقسيط بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سياسة التقسيط غير موجودة.', 404);
        }
    }

    public function storePolicy(InstallmentPolicyRequest $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyResource($this->service->createPolicy($request->validated())),
                'تم إنشاء سياسة التقسيط بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء الإنشاء.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updatePolicy(InstallmentPolicyRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyResource($this->service->updatePolicy($id, $request->validated())),
                'تم تعديل سياسة التقسيط بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سياسة التقسيط غير موجودة.', 404);
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    public function destroyPolicy(int $id): JsonResponse
    {
        try {
            $this->service->deletePolicy($id);
            return $this->successResponse(null, 'تم حذف سياسة التقسيط بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سياسة التقسيط غير موجودة.', 404);
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    public function updatePolicyItem(UpdatePolicyItemRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyItemResource($this->service->updatePolicyItem($id, $request->validated())),
                'تم تعديل تفاصيل الدفعة بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الدفعة غير موجودة.', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroyPolicyItem(int $id): JsonResponse
    {
        try {
            $this->service->deletePolicyItem($id);
            return $this->successResponse(null, 'تم الحذف.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422); 
        }
    }

    // =========================================================
    // ---- واجهات الخطط المالية ----
    // =========================================================

    public function getFeePlans(): JsonResponse
    {
        return $this->successResponse(
            FeePlanResource::collection($this->service->getFeePlans()),
            'تم جلب خطط الرسوم المالية بنجاح.'
        );
    }

    // 👈 إضافة دالة جلب خطة مالية محددة (Show)
    public function showFeePlan(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanResource($this->service->getFeePlanById($id)),
                'تم جلب بيانات الخطة بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('خطة الرسوم غير موجودة.', 404);
        }
    }

    public function storeFeePlan(FeePlanRequest $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanResource($this->service->createFeePlan($request->validated())),
                'تم إنشاء خطة الرسوم بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء الإنشاء.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updateFeePlan(FeePlanRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanResource($this->service->updateFeePlan($id, $request->validated())),
                'تم تعديل خطة الرسوم بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('خطة الرسوم غير موجودة.', 404);
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    public function destroyFeePlan(int $id): JsonResponse
    {
        try {
            $this->service->deleteFeePlan($id);
            return $this->successResponse(null, 'تم حذف خطة الرسوم بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('خطة الرسوم غير موجودة.', 404);
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }
    public function showPolicyItem(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyItemResource($this->service->getInstallmentPolicyItem($id)),
                'تم جلب بيانات الدفعة بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الدفعة غير موجودة.', 404);
        }
    }
        public function showExtraService(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanExtraServiceResource($this->service->getExtraService($id)),
                'تم جلب بيانات الخدمة الإضافية بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الخدمة الإضافية غير موجودة.', 404);
        }
    }

    public function updateExtraService(UpdateExtraServiceRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanExtraServiceResource($this->service->updateExtraService($id, $request->validated())),
                'تم تعديل الخدمة الإضافية بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الخدمة الإضافية غير موجودة.', 404);
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    public function destroyExtraService(int $id): JsonResponse
    {
        try {
            $this->service->deleteExtraService($id);
            return $this->successResponse(null, 'تم حذف الخدمة الإضافية بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الخدمة الإضافية غير موجودة.', 404);
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }
}