<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreGradeConfigurationRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
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
    public function messages(): array
    {
        return [
            'academicYearId.required'           => 'The academic year ID field is required.',
            'academicYearId.exists'             => 'The selected academic year does not exist.',
            
            'grade_level_id.required'           => 'The grade level ID field is required.',
            'grade_level_id.exists'             => 'The selected grade level does not exist.',
            'grade_level_id.unique'             => 'A configuration for this grade level already exists in the specified academic year.',
            
            'supervisor_id.required'            => 'The supervisor ID field is required.',
            'supervisor_id.exists'              => 'The selected supervisor does not exist in the system.',
            
            'planned_classrooms_count.required' => 'The planned classrooms count field is required.',
            'planned_classrooms_count.integer'  => 'The planned classrooms count must be an integer.',
            'planned_classrooms_count.min'      => 'The planned classrooms count must be at least 1.',
            'planned_classrooms_count.max'      => 'The planned classrooms count must not exceed 50.',
        ];
    }
}
