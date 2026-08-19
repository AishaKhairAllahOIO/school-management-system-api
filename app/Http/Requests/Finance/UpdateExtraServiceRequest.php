<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExtraServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return  true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'   => ['sometimes', 'required', 'in:uniform,books,activities,insurance,other'],
            'name'   => ['sometimes', 'required', 'string', 'max:100'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'type.required' => 'The extra service type field is required when present.',
            'type.in'       => 'The selected extra service type is invalid.',
            'name.required' => 'The extra service name field is required when present.',
            'name.string'   => 'The extra service name must be a string.',
            'name.max'      => 'The extra service name must not exceed 100 characters.',
            'amount.required'=> 'The extra service amount field is required when present.',
            'amount.numeric'=> 'The extra service amount must be a number.',
            'amount.min'    => 'The extra service amount must be at least 0.',
        ];
    }
}
