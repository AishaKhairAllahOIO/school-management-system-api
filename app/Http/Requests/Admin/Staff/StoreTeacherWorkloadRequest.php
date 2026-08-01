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
            'required_monthly_periods.min' => 'accepted value must be at least 1.',
        ];
    }
}
