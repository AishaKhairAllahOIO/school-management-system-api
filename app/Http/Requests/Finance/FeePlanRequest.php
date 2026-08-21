<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true ;
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
            'gradeLevelId'             => [
                            'required', 
                            'exists:grade_levels,id',
                            Rule::unique('fee_plans', 'grade_level_id')->where(function ($query) {
                                return $query->where('academic_year_id', $this->input('academicYearId'));
                            })
                        ],        
            'name'                   => ['required', 'string', 'max:100'],
            'baseAmount'             => ['required', 'numeric', 'min:0'],

            'extraServices'          => ['nullable', 'array'],
            'extraServices.*.type'   => ['required', 'in:uniform,books,activities,insurance,other'],
            'extraServices.*.name'   => ['required', 'string', 'max:100'],
            'extraServices.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'academicYearId.required'        => 'The academic year ID field is required.',
            'academicYearId.exists'          => 'The selected academic year does not exist.',
            
            'gradeLevelId.required'          => 'The grade level ID field is required.',
            'gradeLevelId.exists'            => 'The selected grade level does not exist.',
            'gradeLevelId.unique'      => 'A fee plan already exists for this grade level in the selected academic year.',
            'name.required'                  => 'The fee plan name field is required.',
            'name.string'                    => 'The fee plan name must be a string.',
            'name.max'                       => 'The fee plan name must not exceed 100 characters.',
            
            'baseAmount.required'            => 'The base amount field is required.',
            'baseAmount.numeric'             => 'The base amount must be a number.',
            'baseAmount.min'                 => 'The base amount cannot be less than 0.',
            
            'extraServices.array'            => 'Extra services must be provided as an array.',
            'extraServices.*.type.required'  => 'Each extra service must have a type.',
            'extraServices.*.type.in'        => 'The selected extra service type is invalid.',
            'extraServices.*.name.required'  => 'Each extra service must have a name.',
            'extraServices.*.name.string'    => 'The extra service name must be a string.',
            'extraServices.*.name.max'       => 'The extra service name must not exceed 100 characters.',
            'extraServices.*.amount.required'=> 'Each extra service must have an amount.',
            'extraServices.*.amount.numeric' => 'The extra service amount must be a number.',
            'extraServices.*.amount.min'     => 'The extra service amount cannot be less than 0.',
        ];
    }
}
