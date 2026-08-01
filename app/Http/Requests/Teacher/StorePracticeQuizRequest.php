<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StorePracticeQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('teacher');
    }

    public function rules(): array
    {
        return [
            'grade_subject_id'                   => ['required', 'exists:grade_subjects,id'],
            'title'                              => ['required', 'string', 'max:255'],
            'description'                        => ['nullable', 'string'],
            'is_active'                          => ['boolean'],

            'questions'                          => ['required', 'array', 'min:1'],
            'questions.*.question_text'          => ['required', 'string'],
            'questions.*.mark'                   => ['required', 'numeric', 'min:0.5'],

            'questions.*.options'                => ['required', 'array', 'min:2'],
            'questions.*.options.*.option_text'  => ['required', 'string'],
            'questions.*.options.*.is_correct'   => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'questions.*.options.min' => 'Question must has tow options as miniumum',
        ];
    }
}
