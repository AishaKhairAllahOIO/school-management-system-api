<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\GradeName;
use Illuminate\Validation\Rules\Enum;

class UpdateGradeLevelRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
    }


    public function rules(): array
    {

        $gradeId = $this->route('grade_level') ? $this->route('grade_level')->id : null;

        return [
            'academicStageId' => ['sometimes', 'required', 'exists:academic_stages,id'],
            'name' => ['sometimes', new Enum(GradeName::class), 'unique:grade_levels,name'],
            'isGraduationGrade' => ['sometimes', 'boolean'],

        ];

    }
}
