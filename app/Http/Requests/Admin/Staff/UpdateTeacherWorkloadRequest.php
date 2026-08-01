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
}
