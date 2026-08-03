<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentAttendanceSettingRequest extends FormRequest
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
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'working_days' => ['required', 'integer', 'min:1', 'max:150'], 
            'required_attendance_percentage' => ['required', 'numeric', 'min:50', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            'semester_id.required' => 'The semester field is required.',
            'semester_id.exists' => 'The selected semester does not exist.',
            'working_days.required' => 'The working days field is required.',
            'working_days.integer' => 'The working days must be an integer.',
            'working_days.min' => 'The working days must be at least 1.',
            'working_days.max' => 'The working days may not be greater than 150.',
            'required_attendance_percentage.required' => 'The required attendance percentage field is required.',
            'required_attendance_percentage.numeric' => 'The required attendance percentage must be a number.',
            'required_attendance_percentage.min' => 'The required attendance percentage must be at least 50%.',
            'required_attendance_percentage.max' => 'The required attendance percentage may not be greater than 100%.',
        ];
    }
}
