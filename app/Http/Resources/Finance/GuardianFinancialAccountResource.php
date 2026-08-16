<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardianFinancialAccountResource extends JsonResource
{

 public function toArray($request)
{
    return [

        'student'=>[
            'id'=>$this->student->id,
            'name'=>$this->student->user->first_name,
        ],


        'financialSummary'=>[

            'totalAmount'=>
                (float)$this->total_required_amount,


            'paidAmount'=>
                (float)(
                    $this->total_required_amount
                    -
                    $this->remaining_balance
                ),


            'remainingBalance'=>
                (float)$this->remaining_balance,


            'paymentStatus'=>
                $this->payment_status,

        ],


        'transactions'=>
            PaymentTransactionResource::collection(
                $this->whenLoaded('transactions')
            ),

    ];
}
}