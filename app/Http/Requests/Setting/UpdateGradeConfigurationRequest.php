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
}
