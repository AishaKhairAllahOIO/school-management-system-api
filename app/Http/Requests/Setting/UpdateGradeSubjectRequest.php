<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['sometimes', 'integer', 'exists:semesters,id'],
            'grade_level_id'   => ['sometimes', 'integer', 'exists:grade_levels,id'],
            'subject_id'       => ['sometimes', 'integer', 'exists:subjects,id'],

            'weekly_periods'     => ['sometimes', 'integer', 'min:1'],
            'difficulty'         => ['sometimes', 'string', 'in:light,medium,heavy'],

            'max_mark'           => ['sometimes', 'numeric', 'min:1'],
            'passing_mark'       => ['sometimes', 'numeric', 'min:0', 'lte:max_mark'],
            'is_failing_subject' => ['sometimes', 'boolean'],
            'weight_in_total'    => ['sometimes', 'numeric', 'min:0'],

            'max_periods_per_day' => ['sometimes', 'integer', 'min:1'],
            'avoid_first_period' => ['sometimes', 'boolean'],
            'avoid_last_period'  => ['sometimes', 'boolean'],

            'preferred_period_indexes'   => ['nullable', 'array'],
            'preferred_period_indexes.*' => ['integer', 'min:1'],
        ];
    }
}
