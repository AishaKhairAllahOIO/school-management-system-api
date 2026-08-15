<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianFinancialAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'student_id' => $this->student_id,

            'summary' => [
                'total_amount' => (float) $this->total_required_amount,

                'paid_amount' => (float) (
                    $this->total_required_amount 
                    - $this->remaining_balance
                ),

                'remaining_amount' => (float) $this->remaining_balance,

                'status' => $this->payment_status,
            ],


            'plan' => $this->feePlan ? [
                'id' => $this->feePlan->id,
                'name' => $this->feePlan->name,
            ] : null,


            'installment_policy' => $this->installmentPolicy ? [
                'id' => $this->installmentPolicy->id,
                'name' => $this->installmentPolicy->name,
                'count' => $this->installmentPolicy->installments_count,
            ] : null,


            'installments' => $this->whenLoaded(
                'scheduledInstallments',
                function () {

                    return $this->scheduledInstallments
                        ->sortBy('installment_number')
                        ->values()
                        ->map(function ($installment) {

                            return [
                                'id' => $installment->id,

                                'number' =>
                                    $installment->installment_number,

                                'title' =>
                                    $installment->title,

                                'amount' => [
                                    'due' =>
                                        (float) $installment->amount_due,

                                    'paid' =>
                                        (float) $installment->amount_paid,

                                    'remaining' =>
                                        (float) (
                                            $installment->amount_due -
                                            $installment->amount_paid
                                        ),
                                ],


                                'due_date' =>
                                    $installment->due_date?->toDateString(),

                                'status' =>
                                    $installment->status,
                            ];
                        });
                }
            ),


            'transactions' => $this->whenLoaded(
                'transactions',
                function () {

                    return $this->paymentTransactions
                        ->map(function ($transaction) {

                            return [

                                'id' =>
                                    $transaction->id,

                                'amount' =>
                                    (float) $transaction->paid_amount,


                                'payment_method' =>
                                    $transaction->payment_method,


                                'receipt_number' =>
                                    $transaction->paper_receipt_no,


                                'reference' =>
                                    $transaction->digital_reference,


                                'date' =>
                                    $transaction->created_at
                                        ?->toDateString(),

                            ];
                        });
                }
            ),


            'created_at' =>
                $this->created_at?->toISOString(),

        ];
    }
}