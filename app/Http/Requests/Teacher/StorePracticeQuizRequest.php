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
            'grade_subject_id' => ['required', 'exists:grade_subjects,id'],
            'grade_level_id' => ['required', 'integer','exists:grade_levels,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.mark' => ['required', 'numeric', 'min:0.5'],

            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*.option_text' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }

 public function messages(): array
    {
        return [
            'grade_subject_id.required'         => 'The grade subject ID field is required.',
            'grade_subject_id.exists'           => 'The selected grade subject does not exist.',
            'grade_level_id.required'           => 'The grade level ID field is required.',
            'grade_level_id.integer'            => 'The grade level ID must be an integer.',
            'grade_level_id.exists'             => 'The selected grade level does not exist.',
            'title.required'                    => 'The quiz title field is required.',
            'title.string'                      => 'The quiz title must be a string.',
            'title.max'                         => 'The quiz title must not exceed 255 characters.',
            'description.string'                => 'The description must be a string.',
            'is_active.boolean'                 => 'The active status must be true or false.',
            'questions.required'                => 'At least one question is required.',
            'questions.array'                   => 'The questions must be structured as an array.',
            'questions.min'                     => 'The quiz must contain at least 1 question.',
            'questions.*.question_text.required'=> 'Each question must have text.',
            'questions.*.question_text.string'  => 'The question text must be a string.',
            'questions.*.mark.required'         => 'Each question must have a mark.',
            'questions.*.mark.numeric'          => 'The question mark must be a number.',
            'questions.*.mark.min'              => 'The question mark must be at least 0.5.',
            'questions.*.options.required'      => 'Each question must have options.',
            'questions.*.options.array'         => 'Options must be provided as an array.',
            'questions.*.options.min'           => 'Each question must have at least two options.',
            'questions.*.options.*.option_text.required' => 'Each option must have text.',
            'questions.*.options.*.option_text.string' => 'The option text must be a string.',
            'questions.*.options.*.is_correct.required' => 'You must specify whether the option is correct.',
            'questions.*.options.*.is_correct.boolean' => 'The correct status must be true or false.',
        ];
    }
}
