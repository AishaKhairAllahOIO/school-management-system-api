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
            'grade_level_id' => ['sometimes', 'integer', 'exists:grade_levels,id'],
            'subject_id' => ['sometimes', 'integer', 'exists:subjects,id'],

            'weekly_periods' => ['sometimes', 'integer', 'min:1'],
            'difficulty' => ['sometimes', 'string', 'in:light,medium,heavy'],

            'max_mark' => ['sometimes', 'numeric', 'min:1'],
            'passing_mark' => ['sometimes', 'numeric', 'min:0', 'lte:max_mark'],
            'is_failing_subject' => ['sometimes', 'boolean'],
            'weight_in_total' => ['sometimes', 'numeric', 'min:0'],

            'max_periods_per_day' => ['sometimes', 'integer', 'min:1'],
            'avoid_first_period' => ['sometimes', 'boolean'],
            'avoid_last_period' => ['sometimes', 'boolean'],

            'preferred_period_indexes' => ['nullable', 'array'],
            'preferred_period_indexes.*' => ['integer', 'min:1'],
        ];
    }

   public function messages(): array
    {
        return [
            'academic_year_id.integer' => 'The academic year ID must be an integer.',
            'academic_year_id.exists'   => 'The selected academic year does not exist.',
            
            'semester_id.integer'      => 'The semester ID must be an integer.',
            'semester_id.exists'        => 'The selected semester does not exist.',
            
            'grade_level_id.integer'   => 'The grade level ID must be an integer.',
            'grade_level_id.exists'     => 'The selected grade level does not exist.',
            
            'subject_id.integer'       => 'The subject ID must be an integer.',
            'subject_id.exists'         => 'The selected subject does not exist.',
            
            'weekly_periods.integer'   => 'The weekly periods must be an integer.',
            'weekly_periods.min'       => 'The weekly periods must be at least 1.',
            
            'difficulty.string'        => 'The difficulty level must be a string.',
            'difficulty.in'            => 'The selected difficulty level is invalid (must be light, medium, or heavy).',
            
            'max_mark.numeric'         => 'The maximum mark must be a number.',
            'max_mark.min'             => 'The maximum mark must be at least 1.',
            
            'passing_mark.numeric'     => 'The passing mark must be a number.',
            'passing_mark.min'         => 'The passing mark cannot be less than 0.',
            'passing_mark.lte'         => 'The passing mark must be less than or equal to the maximum mark.',
            
            'is_failing_subject.boolean' => 'The failing subject status must be true or false.',
            
            'weight_in_total.numeric'  => 'The weight in total must be a number.',
            'weight_in_total.min'      => 'The weight in total cannot be less than 0.',
            
            'max_periods_per_day.integer' => 'The maximum periods per day must be an integer.',
            'max_periods_per_day.min'  => 'The maximum periods per day must be at least 1.',
            
            'avoid_first_period.boolean' => 'The avoid first period flag must be true or false.',
            'avoid_last_period.boolean'  => 'The avoid last period flag must be true or false.',
            
            'preferred_period_indexes.array' => 'The preferred period indexes must be an array.',
            'preferred_period_indexes.*.integer' => 'Each preferred period index must be an integer.',
            'preferred_period_indexes.*.min' => 'Each preferred period index must be at least 1.',
        ];
    }
}
