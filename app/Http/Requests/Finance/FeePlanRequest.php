<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FeePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('fee:edit') || $this->user()->can('fee:set') || $this->user()->can('fee:view') ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'academicYearId'         => ['required', 'exists:academic_years,id'],
            'gradeLevelId'           => ['required', 'exists:grade_levels,id'],
            'installmentPolicyId'    => ['required', 'exists:installment_policies,id'],
            'name'                   => ['required', 'string', 'max:100'],
            'baseAmount'             => ['required', 'numeric', 'min:0'],
            
            // الخدمات الإضافية (اختيارية)
            'extraServices'          => ['nullable', 'array'],
            'extraServices.*.type'   => ['required', 'in:uniform,books,activities,insurance,other'],
            'extraServices.*.name'   => ['required', 'string', 'max:100'],
            'extraServices.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
