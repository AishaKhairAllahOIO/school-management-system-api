<?php

namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Throwable;
use App\Http\Resources\Staff\PayrollResource;
use App\Http\Requests\Admin\Staff\UpdatePayrollRequest;
use App\Http\Requests\Admin\Staff\CommitPayrollRequest;
use App\Http\Requests\Admin\Staff\PreviewPayrollRequest;
use App\Services\Staff\PayrollService;

class PayrollController extends Controller
{
    use ApiResource;

    protected PayrollService $service;

    public function __construct(PayrollService $service)
    {
        $this->service = $service;
    }

    /**
     * 1️⃣ معاينة الراتب قبل الاعتماد (يحسب ولا يحفظ)
     * ملاحظة: نعيد البيانات كـ مصفوفة خام (Array) وليس Resource لأنه غير مسجل بالـ DB بعد
     */
    public function preview(PreviewPayrollRequest $request): JsonResponse
    {
        try {
            $previewData = $this->service->previewSalary(
                $request->staff_id,
                $request->year,
                $request->month,
                $request->expected_units ?? 30
            );

        return $this->successResponse($previewData, 'Salary preview and deductions calculated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 2️⃣ اعتماد وصرف الراتب (حفظ وإشعار)
     */
    public function store(CommitPayrollRequest $request): JsonResponse
    {
        try {
            $payroll = $this->service->commitSalary($request->validated());
            
            return $this->successResponse(
                new PayrollResource($payroll->load(['staff.user', 'contract'])),
                'Payroll committed and notification sent to staff successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 3️⃣ جلب رواتب جميع الموظفين لشهر وسنة معينة
     */
    public function indexByMonth(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|integer|min:1|max:12',
            ]);

            $payrolls = $this->service->getPayrollsByMonth($request->year, $request->month);
            
            return $this->successResponse(
                PayrollResource::collection($payrolls),
                "Payrolls for month {$request->month} retrieved successfully."
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 4️⃣ جلب أرشيف رواتب موظف معين
     */
    public function getStaffPayrolls(int $staffId): JsonResponse
    {
        try {
            $payrolls = $this->service->getStaffPayrolls($staffId);
            return $this->successResponse(
                PayrollResource::collection($payrolls),
               'Staff payroll history retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 5️⃣ عرض إيصال راتب محدد
     */
    public function show(int $id): JsonResponse
    {
        try {
            $payroll = $this->service->getPayrollById($id);
            return $this->successResponse(new PayrollResource($payroll), 'Payroll details retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Payroll record not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 6️⃣ تعديل بسيط لسجل الراتب (تاريخ الدفع والملاحظات فقط)
     */
    public function update(UpdatePayrollRequest $request, int $id): JsonResponse
    {
        try {
            $payroll = $this->service->updatePayroll($id, $request->validated());
            return $this->successResponse(new PayrollResource($payroll), 'Payroll record updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Payroll record not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 7️⃣ حذف سجل راتب (لإعادة احتسابه)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deletePayroll($id);
            return $this->successResponse(null, 'Payroll receipt deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Payroll record not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
