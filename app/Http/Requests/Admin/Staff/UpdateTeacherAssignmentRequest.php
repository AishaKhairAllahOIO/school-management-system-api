<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherAssignmentRequest extends FormRequest
{
 
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
           return [
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
            'academic_term_id' => ['sometimes', 'integer', 'exists:academic_terms,id'],
            'teacher_id'       => ['sometimes', 'integer', 'exists:staff,id'],
            'grade_subject_id' => ['sometimes', 'integer', 'exists:grade_subjects,id'],
            'classroom_id'     => ['sometimes', 'integer', 'exists:class_rooms,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'academic_year_id.integer' => 'The academic year ID must be an integer.',
            'academic_year_id.exists'   => 'The selected academic year does not exist.',
            'academic_term_id.integer' => 'The academic term ID must be an integer.',
            'academic_term_id.exists'   => 'The selected academic term does not exist.',
            'teacher_id.integer'       => 'The teacher ID must be an integer.',
            'teacher_id.exists'         => 'The selected teacher does not exist.',
            'grade_subject_id.integer' => 'The grade subject ID must be an integer.',
            'grade_subject_id.exists'   => 'The selected grade subject does not exist.',
            'classroom_id.integer'     => 'The classroom ID must be an integer.',
            'classroom_id.exists'       => 'The selected classroom does not exist.',
        ];
    }
    
}
