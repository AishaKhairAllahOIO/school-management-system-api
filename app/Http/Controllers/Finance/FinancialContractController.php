<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinancialContractService;
use App\Http\Requests\Finance\FinancialContractRequest;
use App\Http\Resources\Finance\FinancialAccountResource;
use App\ApiResource;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Finance\ScheduledInstallmentResource;
use App\Models\FinancialAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ScheduledInstallment;
use Throwable;

class FinancialContractController extends Controller
{
    use ApiResource;

    public function __construct(private FinancialContractService $service) {}

    public function installmentsIndex(): JsonResponse
    {
        try {
            $installments = $this->service->getAllInstallments();

            return $this->successResponse(
                ScheduledInstallmentResource::collection($installments),
                'Installments retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض قسط محدد
     */
    public function showInstallment(int $id): JsonResponse
    {
        try {
            $installment = $this->service->getInstallmentById($id);

            return $this->successResponse(
                new ScheduledInstallmentResource($installment),
                'Installment retrieved successfully.' // 💡 تصحيح إملائي بسيط
            );
        } catch (ModelNotFoundException $e) {
            // 💡 تحويل الرسالة إلى الإنجليزية المعيارية
            return $this->errorResponse('The requested installment was not found.', 404);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $accounts = $this->service->getAllAccounts();

            return $this->successResponse(
                FinancialAccountResource::collection($accounts),
                'Finance accounts retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض تفاصيل حساب مالي محدد (باستخدام رقم الطالب)
     */
    public function show(int $studentId): JsonResponse
    {
        try {
            $account = $this->service->getAccountByStudentId($studentId);

            return $this->successResponse(
                new FinancialAccountResource($account),
                // 💡 تصحيح رسالة النجاح التي كانت مكتوبة بالخطأ
                'Finance contract retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Finance contract for this student not found.', 404);
        }
    }

    public function finalize(FinancialContractRequest $request): JsonResponse
    {
        try {
            $account = $this->service->finalizeContract($request->validated());

            return $this->successResponse(
                new FinancialAccountResource($account),
                'Finance contract finalized successfully.',
                201
            );
        } catch (Exception $e) {
            // 💡 التقاط كود الخطأ الدقيق من السيرفيس (422, 409)
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(FinancialContractRequest $request, int $id): JsonResponse
    {
        try {
            $account = $this->service->updateContract($id, $request->validated());

            return $this->successResponse(
                new FinancialAccountResource($account),
                'Finance contract updated successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Contract not found.', 404);
        } catch (Exception $e) {
            // 💡 التقاط كود الخطأ الدقيق من السيرفيس (422, 409)
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $statusCode);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getStudentInstallments(int $studentId): JsonResponse
    {
        try {
            $installments = $this->service->getInstallmentsByStudentId($studentId);

            return $this->successResponse(
                ScheduledInstallmentResource::collection($installments),
                'Student scheduled installments retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Financial account for this student not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}