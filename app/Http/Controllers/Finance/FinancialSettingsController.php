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
use Throwable;

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
            'Installment policies retrieved successfully.'
        );
    }

    public function showPolicy(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyResource($this->service->getPolicyById($id)),
                'Installment policy retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment policy not found', 404);
        }
    }

    public function storePolicy(InstallmentPolicyRequest $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyResource($this->service->createPolicy($request->validated())),
                'Installment policy created successfully.',
                201
            );
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updatePolicy(InstallmentPolicyRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyResource($this->service->updatePolicy($id, $request->validated())),
                'Installment policy updated successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment policy not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroyPolicy(int $id): JsonResponse
    {
        try {
            $this->service->deletePolicy($id);
            return $this->successResponse(null, 'Installment policy deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment policy not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updatePolicyItem(UpdatePolicyItemRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyItemResource($this->service->updatePolicyItem($id, $request->validated())),
                'Installment policy item updated successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment policy item not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroyPolicyItem(int $id): JsonResponse
    {
        try {
            $this->service->deletePolicyItem($id);
            return $this->successResponse(null, 'Installment policy item deleted successfully.');
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    // =========================================================
    // ---- واجهات الخطط المالية ----
    // =========================================================

    public function getFeePlans(): JsonResponse
    {
        return $this->successResponse(
            FeePlanResource::collection($this->service->getFeePlans()),
            'Installment fee plans retrieved successfully.'
        );
    }

    public function showFeePlan(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanResource($this->service->getFeePlanById($id)),
                'Installment fee plan retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment fee plan not found', 404);
        }
    }

    public function storeFeePlan(FeePlanRequest $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanResource($this->service->createFeePlan($request->validated())),
                'Installment fee plan created successfully.',
                201
            );
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updateFeePlan(FeePlanRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanResource($this->service->updateFeePlan($id, $request->validated())),
                'Installment fee plan updated successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment fee plan not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroyFeePlan(int $id): JsonResponse
    {
        try {
            $this->service->deleteFeePlan($id);
            return $this->successResponse(null, 'Installment fee plan deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment fee plan not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function showPolicyItem(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new InstallmentPolicyItemResource($this->service->getInstallmentPolicyItem($id)),
                'Installment policy item retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment policy item not found', 404);
        }
    }

    public function showExtraService(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanExtraServiceResource($this->service->getExtraService($id)),
                'Installment extra service retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment extra service not found', 404);
        }
    }

    public function updateExtraService(UpdateExtraServiceRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                new FeePlanExtraServiceResource($this->service->updateExtraService($id, $request->validated())),
                'Installment extra service updated successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment extra service not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroyExtraService(int $id): JsonResponse
    {
        try {
            $this->service->deleteExtraService($id);
            return $this->successResponse(null, 'Installment extra service deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Installment extra service not found', 404);
        } catch (Exception $e) {
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}