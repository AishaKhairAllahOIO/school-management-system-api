<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو الصلاحية الخاصة بك
    }

    public function rules(): array
    {
        return [
            'studentId'         => ['required', 'exists:students,id'],
            'paidAmount'        => ['required', 'numeric', 'min:1'],
            'paymentMethod'     => ['required', 'in:cash,bank_transfer,cheque,electronic_wallet'],
            'paperReceiptNo'    => ['nullable', 'string', 'max:50'],
            'digitalReference'  => ['nullable', 'string', 'max:100'],
        ];
    }
}