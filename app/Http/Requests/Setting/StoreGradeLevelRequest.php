<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeLevelRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
    }


    public function rules(): array
    {


        return [
            'academicStageId' => ['required', 'exists:academic_stages,id'],
            'name' => ['required', 'string', 'max:100', 'unique:grade_levels,name'],
            'isGraduationGrade' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_levels.*.grade_name.distinct' => 'accepted grade names must be unique within the request.',
            'grade_levels.*.grade_name.unique' => 'Grade name already exists.',
            'grade_levels.*.classrooms.*.capacity.min' => 'Classroom capacity must be at least 1.',
        ];
    }
}
