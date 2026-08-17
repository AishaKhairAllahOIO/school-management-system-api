<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\GradeName;
use Illuminate\Validation\Rules\Enum;
class StoreGradeLevelRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {


        return [
            'academicStageId' => ['required', 'exists:academic_stages,id'],
            'name' => ['required', new Enum(GradeName::class), 'unique:grade_levels,name'],
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
