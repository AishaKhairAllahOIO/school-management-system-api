<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreGradeConfigurationRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
    }

    public function rules(): array
    {
        return [
            'academicYearId' => ['required', 'exists:academic_years,id'],
            'grade_level_id' => [
                'required',
                'exists:grade_levels,id',
                Rule::unique('grade_configurations', 'grade_level_id')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academicYearId);
                })
            ],
            'supervisor_id' => ['required', 'exists:users,id'],
            'planned_classrooms_count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
