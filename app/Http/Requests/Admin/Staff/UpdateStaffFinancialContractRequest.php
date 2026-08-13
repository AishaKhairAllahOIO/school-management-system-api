<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffFinancialContractRequest extends FormRequest
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
            'staff_id'         => ['sometimes',  'integer', 'exists:staff,id'],
            'academic_year_id' => ['sometimes',  'integer', 'exists:academic_years,id'],
            'salary_type'      => ['sometimes',  'string', 'in:per_period,fixed_monthly'],
            'salary_amount'    => ['sometimes',  'numeric', 'min:0'],
        ];
    }
}
