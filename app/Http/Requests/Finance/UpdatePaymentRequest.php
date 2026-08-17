<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paidAmount'        => ['nullable', 'numeric'],
            'paymentMethod'     => ['sometimes', 'required', 'in:cash,bank_transfer,cheque,electronic_wallet'],
            'paperReceiptNo'    => ['nullable', 'string', 'max:50'],
            'digitalReference'  => ['nullable', 'string', 'max:100'],
        ];
    }
}
