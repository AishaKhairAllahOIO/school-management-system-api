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
use Illuminate\Database\Eloquent\ModelNotFoundException;


class FinancialContractController extends Controller
{
    use ApiResource;

    public function __construct(private FinancialContractService $service) {}


    
    public function installmentsIndex()
    {
        try {
            $installments = $this->service->getAllInstallments();

            return $this->successResponse(
                ScheduledInstallmentResource::collection($installments),
                'تم جلب جميع الأقساط بنجاح.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب الأقساط', 500, ['error' => $e->getMessage()]);
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
                'تم جلب تفاصيل القسط بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('القسط المطلوب غير موجود.', 404);
        }
    }
     public function index()
    {
        try {
            $accounts = $this->service->getAllAccounts();

            return $this->successResponse(
                FinancialAccountResource::collection($accounts),
                'تم جلب جميع الحسابات المالية بنجاح.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب البيانات', 500, ['error' => $e->getMessage()]);
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
                'تم جلب تفاصيل الحساب المالي بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('لا يوجد حساب مالي مسجل لهذا الطالب.', 404);
        }
    }
    
    public function finalize(FinancialContractRequest $request): JsonResponse
    {
        try {
            $account = $this->service->finalizeContract($request->validated());

            return $this->successResponse(
                new FinancialAccountResource($account),
                'تم اعتماد العقد المالي وتوليد الأقساط الزمنية بنجاح.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطاء اثناء العملية', 422,$e->getMessage());
        }
    }
        public function update(FinancialContractRequest $request, int $id): JsonResponse
    {
        try {
            $account = $this->service->updateContract($id, $request->validated());

            return $this->successResponse(
                new FinancialAccountResource($account),
                'تم تعديل العقد المالي وإعادة توليد الأقساط بنجاح.'
            );
        }catch(ModelNotFoundException $e){
            return $this->errorResponse('لا يوجد حساب مالي مسجل لهذا الطالب.', 404);
        }
         catch (Exception $e) {
            return $this->errorResponse('حدث خطاء اثناء العملية', 422,$e->getMessage());
        }
    }
}