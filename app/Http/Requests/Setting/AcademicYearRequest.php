<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class AcademicYearRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $rules = [
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'isCurrent' => 'required|boolean',
        ];
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(fn($rule) => 'sometimes|' . $rule, $rules);
        }
        return $rules;
    }
    public function messages(): array
    {
        return [
            'startDate.required' => 'The start date field is required.',
            'startDate.date'     => 'The start date must be a valid date.',
            
            'endDate.required'   => 'The end date field is required.',
            'endDate.date'       => 'The end date must be a valid date.',
            'endDate.after'      => 'The end date must be a date after the start date.',
            
            'isCurrent.required' => 'The is current field is required.',
            'isCurrent.boolean'  => 'The is current field must be true or false.',
        ];
    }
}
