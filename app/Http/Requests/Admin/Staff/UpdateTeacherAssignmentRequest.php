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
}
