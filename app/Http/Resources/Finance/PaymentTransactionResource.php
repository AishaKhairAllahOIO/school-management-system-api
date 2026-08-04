<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string) $this->id,
            'accountId'         => (string) $this->financial_account_id,
            'studentId'         =>$this->account? $this->account->student_id : null,
            'paidAmount'        => (float) $this->paid_amount,
            'paymentMethod'     => $this->payment_method,
            'paperReceiptNo'    => $this->paper_receipt_no,
            'digitalReference'  => $this->digital_reference,
            
            // جلب اسم المحاسب الذي استلم المبلغ
            'user_id'       => $this->collected_by_user_id,
            'createdAt'                  => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->toIso8601String() : null,
            'updatedAt'                  => $this->updated_at ? \Carbon\Carbon::parse($this->updated_at)->toIso8601String() : null,
                    
        ];
    }
}