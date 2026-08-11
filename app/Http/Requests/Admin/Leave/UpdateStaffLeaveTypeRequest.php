<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'name'                       => ['sometimes', 'string', 'max:100', 'unique:staff_leave_types,name,' . $this->route('leave_type')],
            'payment_type'               => ['sometimes', 'string', 'in:paid,unpaid'],
            'max_days_per_academic_year' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
