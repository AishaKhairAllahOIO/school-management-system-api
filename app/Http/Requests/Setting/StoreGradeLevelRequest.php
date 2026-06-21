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
            'grade_levels'                          => ['required', 'array', 'min:1'],
            'grade_levels.*.grade_name'                   => ['required', 'string', 'max:255', 'distinct', Rule::unique('grade_levels', 'grade_name'),],

            'grade_levels.*.classrooms'             => ['required', 'array', 'min:1'],
            'grade_levels.*.classrooms.*.name'      => ['nullable', 'string', 'max:255'],
            'grade_levels.*.classrooms.*.capacity'  => ['required', 'integer', 'min:10', 'max:1000'],
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
