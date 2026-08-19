<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
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
    public function messages(): array
    {
        return [
            'studentId.required'     => 'The student ID field is required.',
            'studentId.exists'       => 'The selected student does not exist in the system.',
            'paidAmount.required'    => 'The paid amount field is required.',
            'paidAmount.numeric'     => 'The paid amount must be a number.',
            'paidAmount.min'         => 'The paid amount must be at least 1.',
            'paymentMethod.required' => 'The payment method field is required.',
            'paymentMethod.in'       => 'The selected payment method is invalid.',
            'paperReceiptNo.string'  => 'The paper receipt number must be a string.',
            'paperReceiptNo.max'     => 'The paper receipt number must not exceed 50 characters.',
            'digitalReference.string'=> 'The digital reference must be a string.',
            'digitalReference.max'   => 'The digital reference must not exceed 100 characters.',
        ];
    }
}
