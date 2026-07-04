<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeLevelRequest extends FormRequest
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


        return [
         'academicStageId'   => ['required', 'exists:academic_stages,id'],
            'name'              => ['required', 'string', 'max:100', 'unique:grade_levels,name'],
            'isGraduationGrade' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_levels.*.grade_name.distinct'           => 'يوجد اسم مرحلة دراسية مكرر في الطلب.',
            'grade_levels.*.grade_name.unique'   => 'اسم المرحلة الدراسية موجود مسبقاً.',
            'grade_levels.*.classrooms.*.capacity.min' => 'سعة الشعبة يجب أن تكون 1 على الأقل.',
        ];
    }
}
