<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('student');
    }

    public function rules(): array
    {
        return [
            'answers'                 => ['required', 'array', 'min:1'],
            'answers.*.question_id'   => ['required', 'integer', 'exists:questions,id'],
            'answers.*.option_id'     => ['required', 'integer', 'exists:options,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required'               => 'You must submit at least one answer.',
            'answers.array'                  => 'The answers must be provided as a list.',
            'answers.min'                    => 'You must answer at least one question.',
            'answers.*.question_id.required' => 'The question ID is missing for one of the answers.',
            'answers.*.question_id.exists'   => 'One of the provided questions does not exist.',
            'answers.*.option_id.required'   => 'An option ID is required for each answered question.',
            'answers.*.option_id.exists'     => 'One of the selected options is invalid.',
        ];
    }
}
