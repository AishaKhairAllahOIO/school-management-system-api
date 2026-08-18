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
    public function messages(): array
    {
        return [
            'staff_id.required'         => 'The staff ID is required.',
            'staff_id.integer'          => 'The staff ID must be an integer.',
            'staff_id.exists'           => 'The selected staff member does not exist in the system.',

            'academic_year_id.required' => 'The academic year ID is required.',
            'academic_year_id.integer'  => 'The academic year ID must be an integer.',
            'academic_year_id.exists'   => 'The selected academic year does not exist in the system.',

            'salary_type.required'      => 'The salary type is required.',
            'salary_type.string'        => 'The salary type must be a valid string.',
            'salary_type.in'            => 'The salary type must be either per_period or fixed_monthly.',

            'salary_amount.required'    => 'The salary amount is required.',
            'salary_amount.numeric'     => 'The salary amount must be a numeric value.',
            'salary_amount.min'         => 'The salary amount cannot be less than 0.',
        ];
    }
}
