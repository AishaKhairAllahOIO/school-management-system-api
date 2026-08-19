<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      return [
            'payment_date' => ['sometimes', 'required', 'date'],
        ];
    }
    public function messages(): array
    {
        return [
            'payment_date.required' => 'The payment date field is required when provided.',
            'payment_date.date'     => 'The payment date must be a valid date format.',
        ];
    }
}
