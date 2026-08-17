<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\PaymentService;
use App\Http\Requests\Finance\PaymentTransactionRequest;
use App\Http\Resources\Finance\PaymentTransactionResource;
use App\Http\Resources\Finance\FinancialAccountResource;
use App\ApiResource;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Finance\UpdatePaymentRequest;

class PaymentController extends Controller
{
    use ApiResource;

    public function __construct(private PaymentService $service) {}


     public function index()
    {
        try {
            $payments = $this->service->getAllPayments();

            return $this->successResponse(
                PaymentTransactionResource::collection($payments),
                'تم جلب إيصالات الدفع بنجاح.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب البيانات', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔍 عرض تفاصيل إيصال دفع محدد
     */
    public function show(int $id): JsonResponse
    {
        try {
            $payment = $this->service->getPaymentById($id);

            return $this->successResponse(
                new PaymentTransactionResource($payment),
                'تم جلب تفاصيل الإيصال بنجاح.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('إيصال الدفع غير موجود.', 404);
        }
    }
    public function store(PaymentTransactionRequest $request): JsonResponse
    {
        try {
            $result = $this->service->processPayment($request->validated());

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل الدفعة بنجاح وتحديث الأقساط.',
                'data'    => [
                    'receipt' => new PaymentTransactionResource($result['transaction']),
                    'account' => new FinancialAccountResource($result['account'])
                ]
            ], 201);

        } catch (Exception $e) {
            $statusCode = in_array($e->getCode(), [404, 422]) ? $e->getCode() : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }
     public function update(UpdatePaymentRequest $request, int $id): JsonResponse
    {
        try {
            $transaction = $this->service->updatePayment($id, $request->validated());

            return $this->successResponse(
                new PaymentTransactionResource($transaction),
                'تم تعديل بيانات الإيصال بنجاح.'
            );
        } catch (Exception $e) {
            // سيلتقط كود 422 إذا حاول تعديل المبلغ المالي
            $statusCode = $e->getCode() == 422 ? 422 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * 🗑️ حذف الإيصال وعكس التأثير المالي (Reverse Waterfall)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deletePayment($id);

            return $this->successResponse(
                null,
                'تم حذف الإيصال وعكس حركته من الأقساط ورصيد الطالب بنجاح.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('الإيصال المحدد غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف الإيصال: ' . $e->getMessage(), 500);
        }
    }
}