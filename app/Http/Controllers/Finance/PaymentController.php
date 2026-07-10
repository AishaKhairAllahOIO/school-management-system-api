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

class PaymentController extends Controller
{
    use ApiResource;

    public function __construct(private PaymentService $service) {}

    /**
     * تسجيل دفعة مالية جديدة للطالب (الصندوق)
     */
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
}