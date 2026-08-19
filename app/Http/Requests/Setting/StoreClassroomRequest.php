<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'academicYearId' => ['required', 'exists:academic_years,id'],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'capacity' => ['required', 'integer', 'min:5', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            'academicYearId.required' => 'The academic year ID field is required.',
            'academicYearId.exists'   => 'The selected academic year does not exist.',
            
            'grade_level_id.required' => 'The grade level ID field is required.',
            'grade_level_id.exists'   => 'The selected grade level does not exist.',
            
            'capacity.required'       => 'The classroom capacity field is required.',
            'capacity.integer'        => 'The classroom capacity must be an integer.',
            'capacity.min'            => 'The classroom capacity must be at least 5 students.',
            'capacity.max'            => 'The classroom capacity must not exceed 100 students.',
        ];
    }
}
