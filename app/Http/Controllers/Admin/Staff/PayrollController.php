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

            return $this->successResponse($previewData, 'تمت معاينة الراتب واحتساب الخصميات بنجاح.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء معاينة الراتب.', 500, ['error' => $e->getMessage()]);
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
                'تم حفظ واعتماد الراتب وإرسال الإشعار للموظف بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء اعتماد وصرف الراتب.', 500, ['error' => $e->getMessage()]);
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
                "تم جلب رواتب شهر {$request->month} بنجاح."
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب الرواتب.', 500, ['error' => $e->getMessage()]);
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
                'تم جلب سجل رواتب الموظف بنجاح.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب رواتب الموظف.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 5️⃣ عرض إيصال راتب محدد
     */
    public function show(int $id): JsonResponse
    {
        try {
            $payroll = $this->service->getPayrollById($id);
            return $this->successResponse(new PayrollResource($payroll), 'تم جلب تفاصيل الراتب بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الراتب غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب سجل الراتب.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 6️⃣ تعديل بسيط لسجل الراتب (تاريخ الدفع والملاحظات فقط)
     */
    public function update(UpdatePayrollRequest $request, int $id): JsonResponse
    {
        try {
            $payroll = $this->service->updatePayroll($id, $request->validated());
            return $this->successResponse(new PayrollResource($payroll), 'تم تعديل سجل الراتب بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الراتب غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل سجل الراتب.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 7️⃣ حذف سجل راتب (لإعادة احتسابه)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deletePayroll($id);
            return $this->successResponse(null, 'تم حذف إيصال الراتب بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل الراتب غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف سجل الراتب.', 500, ['error' => $e->getMessage()]);
        }
    }
}
