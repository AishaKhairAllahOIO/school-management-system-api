<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'], // أو semesters
            'grade_level_id'   => ['required', 'integer', 'exists:grade_levels,id'],
            'subject_id'       => [
                'required',
                'integer',
                'exists:subjects,id',
                Rule::unique('grade_subjects')->where(function ($query) {
                    return $query->where('semester_id', $this->semester_id)
                        ->where('grade_level_id', $this->grade_level_id);
                })
            ],

            'weekly_periods'     => ['required', 'integer', 'min:1'],
            'difficulty'         => ['required', 'string', 'in:light,medium,heavy'],

            'max_mark'           => ['required', 'numeric', 'min:1'],
            'passing_mark'       => ['required', 'numeric', 'min:0', 'lte:max_mark'],
            'is_failing_subject' => ['required', 'boolean'],
            'weight_in_total'    => ['required', 'numeric', 'min:0'],

            'max_periods_per_day' => ['required', 'integer', 'min:1'],
            'avoid_first_period' => ['required', 'boolean'],
            'avoid_last_period'  => ['required', 'boolean'],
            'preferred_period_indexes'   => ['nullable', 'array'],
            'preferred_period_indexes.*' => ['integer', 'min:1'],
        ];
    }
}
