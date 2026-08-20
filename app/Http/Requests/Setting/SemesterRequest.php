<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class SemesterRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $rules = [
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterName' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'isCurrent' => 'required|boolean',
            'isFinalTerm' => 'required|boolean',
        ];
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(fn($rule) => 'sometimes|' . $rule, $rules);
        }
        return $rules;
    }
    public function messages(): array
    {
        return [
            'academicYearId.required' => 'The academic year ID field is required.',
            'academicYearId.exists'   => 'The selected academic year does not exist.',
            
            'semesterName.required'   => 'The semester name field is required.',
            'semesterName.string'     => 'The semester name must be a string.',
            'semesterName.max'        => 'The semester name must not exceed 255 characters.',
            
            'startDate.required'      => 'The start date field is required.',
            'startDate.date'          => 'The start date must be a valid date.',
            
            'endDate.required'        => 'The end date field is required.',
            'endDate.date'            => 'The end date must be a valid date.',
            'endDate.after'           => 'The end date must be a date after the start date.',
            
            'isCurrent.required'      => 'The is current field is required.',
            'isCurrent.boolean'       => 'The is current field must be true or false.',
            
            'isFinalTerm.required'    => 'The is final term field is required.',
            'isFinalTerm.boolean'     => 'The is final term field must be true or false.',
        ];
    }
}
