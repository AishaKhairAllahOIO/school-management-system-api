<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherWorkloadRequest extends FormRequest
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
            'academic_year_id'         => ['required', 'exists:academic_years,id'],
            'teacher_id'               => ['required', 'exists:staff,id'],
            'required_monthly_periods' => ['required', 'integer', 'min:1'],
        ];
    }

   public function messages(): array
    {
        return [
            'academic_year_id.required'         => 'The academic year ID is required.',
            'academic_year_id.exists'           => 'The selected academic year does not exist in the system.',

            'teacher_id.required'               => 'The teacher ID is required.',
            'teacher_id.exists'                 => 'The selected teacher staff member does not exist.',

            'required_monthly_periods.required' => 'The required monthly periods count is required.',
            'required_monthly_periods.integer'  => 'The required monthly periods must be an integer.',
            'required_monthly_periods.min'      => 'The accepted value must be at least 1.',
        ];
    }
}
