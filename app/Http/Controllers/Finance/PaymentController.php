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
                'Payments retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
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
                'Payment transaction retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Payment transaction not found', 404);
        }
    }
    public function store(PaymentTransactionRequest $request): JsonResponse
    {
        try {
            $result = $this->service->processPayment($request->validated());

            return response()->json([
                'status'  => true,
                'message' => 'Payment registered successfully and installments updated.',
                'data'    => [
                    'receipt' => new PaymentTransactionResource($result['transaction']),
                    'account' => new FinancialAccountResource($result['account'])
                ]
            ], 201);

        } catch (Exception $e) {
            $statusCode = in_array($e->getCode(), [404, 422]) ? $e->getCode() : 500;
            return $this->errorResponse('Error:Server', $statusCode);
        }
    }
     public function update(UpdatePaymentRequest $request, int $id): JsonResponse
    {
        try {
            $transaction = $this->service->updatePayment($id, $request->validated());

            return $this->successResponse(
                new PaymentTransactionResource($transaction),
                'Payment transaction updated successfully.'
            );
        } catch (Exception $e) {
            // سيلتقط كود 422 إذا حاول تعديل المبلغ المالي
            $statusCode = $e->getCode() == 422 ? 422 : 500;
            return $this->errorResponse('Error:Server', $statusCode);
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
                'Payment transaction deleted successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Payment transaction not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }
}