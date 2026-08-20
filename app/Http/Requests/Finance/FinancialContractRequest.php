<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class FinancialContractRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId'               => ['required', 'exists:students,id'],
            'academicYearId'          => ['required', 'exists:academic_years,id'],
            'feePlanId'               => ['required', 'exists:fee_plans,id'],
            'installmentPolicyId'     => ['required', 'exists:installment_policies,id'],
            'selectedExtraServiceIds' => ['nullable', 'array'],
            'selectedExtraServiceIds.*'=> ['exists:fee_plan_extra_services,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'studentId.required'              => 'The student ID field is required.',
            'studentId.exists'                => 'The selected student does not exist.',
            
            'academicYearId.required'         => 'The academic year ID field is required.',
            'academicYearId.exists'           => 'The selected academic year does not exist.',
            
            'feePlanId.required'              => 'The fee plan ID field is required.',
            'feePlanId.exists'                => 'The selected fee plan does not exist.',
            
            'installmentPolicyId.required'    => 'The installment policy ID field is required.',
            'installmentPolicyId.exists'      => 'The selected installment policy does not exist.',
            
            'selectedExtraServiceIds.array'   => 'The extra services must be provided as an array.',
            'selectedExtraServiceIds.*.exists' => 'One or more selected extra services are invalid or do not exist.',
        ];
    }
}
