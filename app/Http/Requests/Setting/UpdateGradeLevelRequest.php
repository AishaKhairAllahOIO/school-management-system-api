<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradeLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       return $this->user()->can('school:initialize');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $gradeId = $this->route('grade_level') ? $this->route('grade_level')->id : null;

        return [
            'academicStageId'   => ['sometimes', 'required', 'exists:academic_stages,id'],
            'name'              => ['sometimes', 'required', 'string', 'max:100', Rule::unique('grade_levels')->ignore($gradeId)],
            'isGraduationGrade' => ['sometimes', 'boolean'],
            
        ];

    }
}
