<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherWorkloadRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
      return [
            'academic_year_id'         => ['sometimes', 'integer', 'exists:academic_years,id'],
            'teacher_id'               => ['sometimes', 'integer', 'exists:staff,id'],
            'required_monthly_periods' => ['sometimes', 'integer', 'min:1'],
        ];
    }
    public function messages(): array
    {
        return [
            'academic_year_id.integer' => 'The academic year ID must be an integer.',
            'academic_year_id.exists'   => 'The selected academic year does not exist.',
            'teacher_id.integer'       => 'The teacher ID must be an integer.',
            'teacher_id.exists'         => 'The selected teacher does not exist.',
            'required_monthly_periods.integer' => 'The required monthly periods must be an integer.',
            'required_monthly_periods.min'     => 'The required monthly periods must be at least 1.',
        ];
    }
    
}
