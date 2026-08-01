<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherAssignmentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


 public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'teacher_id'       => ['required', 'exists:staff,id'],

            'grade_subject_id' => ['required', 'exists:grade_subjects,id'],

            'class_room_ids'    => ['required', 'array', 'min:1'],
            'class_room_ids.*'  => ['integer', 'exists:class_rooms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_ids.required' => 'You must select at least one classroom for the teacher assignment.',
            'grade_subject_id.required' => 'The grade_subject_id field is required.',
            'grade_subject_id.exists' => 'The selected grade_subject_id is invalid. Please select a valid grade_subject_id.',
        ];
    }
}
