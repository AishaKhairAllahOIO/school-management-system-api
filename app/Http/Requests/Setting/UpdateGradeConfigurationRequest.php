<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeConfigurationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_level_id' => ['sometimes', 'exists:grade_levels,id'],
            'supervisor_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'planned_classrooms_count' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],

        ];
    }
    public function messages(): array
    {
        return [
            'grade_level_id.exists'           => 'The selected grade level does not exist.',
            
            'supervisor_id.exists'            => 'The selected supervisor does not exist in the system.',
            
            'planned_classrooms_count.required' => 'The planned classrooms count field is required when present.',
            'planned_classrooms_count.integer'  => 'The planned classrooms count must be an integer.',
            'planned_classrooms_count.min'      => 'The planned classrooms count must be at least 1.',
            'planned_classrooms_count.max'      => 'The planned classrooms count must not exceed 50.',
        ];
    }
}
