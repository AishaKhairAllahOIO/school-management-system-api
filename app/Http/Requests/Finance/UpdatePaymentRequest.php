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
    public function messages(): array
    {
        return [
            'paidAmount.numeric'     => 'The paid amount must be a number.',
            'paymentMethod.required' => 'The payment method field is required when present.',
            'paymentMethod.in'       => 'The selected payment method is invalid (must be cash, bank_transfer, cheque, or electronic_wallet).',
            'paperReceiptNo.string'  => 'The paper receipt number must be a string.',
            'paperReceiptNo.max'     => 'The paper receipt number must not exceed 50 characters.',
            'digitalReference.string'=> 'The digital reference must be a string.',
            'digitalReference.max'   => 'The digital reference must not exceed 100 characters.',
        ];
    }
}
