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
}
