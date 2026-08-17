<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardianFinancialAccountResource extends JsonResource
{

    public function toArray($request)
    {
        return [

            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->user->first_name,
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


            'financialSummary' => [

                'totalAmount' =>
                    (float) $this->total_required_amount,


                'paidAmount' =>
                    (float) (
                        $this->total_required_amount
                        -
                        $this->remaining_balance
                    ),


                'remainingBalance' =>
                    (float) $this->remaining_balance,


                'paymentStatus' =>
                    $this->payment_status,

            ],


            'transactions' =>
                PaymentTransactionResource::collection(
                    $this->whenLoaded('transactions')
                ),

        ];
    }
}
