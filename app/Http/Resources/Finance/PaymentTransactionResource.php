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
            'paidAmount'        => (float) $this->paid_amount,
            'paymentMethod'     => $this->payment_method,
            'paperReceiptNo'    => $this->paper_receipt_no,
            'digitalReference'  => $this->digital_reference,
            
            // جلب اسم المحاسب الذي استلم المبلغ
            'user_id'       => $this->collected_by_user_id,
            
        ];
    }
}