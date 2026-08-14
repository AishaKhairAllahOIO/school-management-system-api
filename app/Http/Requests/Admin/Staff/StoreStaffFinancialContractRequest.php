<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffFinancialContractRequest extends FormRequest
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
            'staff_id'         => ['required', 'integer', 'exists:staff,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'salary_type'      => ['required', 'string', 'in:per_period,fixed_monthly'],
            'salary_amount'    => ['required', 'numeric', 'min:0'],
        ];
    }
}
