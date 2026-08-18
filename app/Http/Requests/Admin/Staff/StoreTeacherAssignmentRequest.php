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
            'academic_year_id.required'  => 'The academic year ID is required.',
            'academic_year_id.exists'    => 'The selected academic year does not exist.',

            'semester_id.required'       => 'The semester ID is required.',
            'semester_id.exists'         => 'The selected semester does not exist.',

            'teacher_id.required'        => 'The teacher ID is required.',
            'teacher_id.exists'          => 'The selected teacher staff member does not exist.',

            'grade_subject_id.required'  => 'The grade subject ID is required.',
            'grade_subject_id.exists'    => 'The selected grade subject is invalid. Please select a valid one.',

            'class_room_ids.required'    => 'You must select at least one classroom for the teacher assignment.',
            'class_room_ids.array'       => 'The classrooms format must be an array.',
            'class_room_ids.min'         => 'You must select at least one classroom.',
            'class_room_ids.*.integer'   => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'    => 'One or more selected classrooms do not exist in the system.',
        ];
    }
}
