<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class FinancialContractRequest extends BaseRequest
{
    public function authorize(): bool
    {
        // يمكن لاحقاً تقييدها بـ role المحاسب أو المدير
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
}