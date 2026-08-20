<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('teacher') || $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'grade_subject_id'                     => ['required', 'exists:grade_subjects,id'],
            'class_room_id'                        => ['required', 'exists:class_rooms,id'],

            'marks'                                => ['required', 'array', 'min:1'],
            'marks.*.enrollment_id'                => ['required', 'exists:enrollments,id'],
            'marks.*.assessment_component_id'      => ['required', 'exists:assessment_components,id'],
            'marks.*.mark'                         => ['nullable', 'numeric', 'min:0'],
            'marks.*.notes'                        => ['nullable', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        return [
            'grade_subject_id.required'                => 'The grade subject ID field is required.',
            'grade_subject_id.exists'                  => 'The selected grade subject does not exist.',
            'class_room_id.required'                   => 'The classroom ID field is required.',
            'class_room_id.exists'                     => 'The selected classroom does not exist.',
            'marks.required'                           => 'At least one mark record is required.',
            'marks.array'                              => 'Marks must be provided as an array.',
            'marks.min'                                => 'The marks list must contain at least 1 record.',
            'marks.*.enrollment_id.required'           => 'Each mark record must specify a student enrollment.',
            'marks.*.enrollment_id.exists'             => 'One or more student enrollments do not exist.',
            'marks.*.assessment_component_id.required' => 'Each mark record must specify an assessment component.',
            'marks.*.assessment_component_id.exists'   => 'One or more assessment components do not exist.',
            'marks.*.mark.numeric'                     => 'The mark value must be a number.',
            'marks.*.mark.min'                         => 'The mark cannot be less than 0.',
            'marks.*.notes.string'                     => 'The notes must be a string.',
            'marks.*.notes.max'                        => 'The notes must not exceed 255 characters.',
        ];
    }
}
