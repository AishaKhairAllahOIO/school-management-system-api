<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateStaffLeaveTypeRequest extends FormRequest
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
            'name'                       => ['sometimes', 'string', 'max:100',Rule::unique('staff_leave_types', 'name')->ignore($this->route('id'))],
            'payment_type'               => ['sometimes', 'string', 'in:paid,unpaid'],
            'max_days_per_academic_year' => ['sometimes', 'integer', 'min:0'],
        ];
    }
    public function messages(){
     return [
            'name.string' => 'The leave type name must be a valid string.',
            'name.max'    => 'The leave type name must not exceed 100 characters.',
            'name.unique' => 'This leave type name has already been taken.',

            'payment_type.string' => 'The payment type must be a valid string.',
            'payment_type.in'     => 'The payment type must be either paid or unpaid.',

            'max_days_per_academic_year.integer' => 'The maximum allowed days per academic year must be an integer.',
            'max_days_per_academic_year.min'     => 'The maximum allowed days cannot be less than 0.',
        ];
    }
}
