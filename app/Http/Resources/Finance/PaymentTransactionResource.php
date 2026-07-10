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
            'cashierName'       => $this->cashier ? $this->cashier->first_name . ' ' . $this->cashier->last_name : 'النظام الآلي',
            
            'paymentDate'       => Carbon::parse($this->created_at)->format('Y-m-d H:i A'),
        ];
    }
}