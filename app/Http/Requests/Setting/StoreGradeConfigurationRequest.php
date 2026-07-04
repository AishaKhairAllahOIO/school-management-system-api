<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreGradeConfigurationRequest extends FormRequest
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
            'academicYearId' => ['required', 'exists:academic_years,id'],
            'grade_level_id'        => [
                'required', 
                'exists:grade_levels,id',
                // 🛡️ حماية: منع إضافة نفس الصف مرتين في نفس العام الدراسي!
                Rule::unique('grade_configurations', 'grade_level_id')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academicYearId);
                })
            ],
            'supervisor_id'           => ['required', 'exists:users,id'],
            'planned_classrooms_count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
