<?php

namespace App\Http\Requests\Counselor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCounselingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_status' => [
                'required',
                Rule::in([
                    'present',
                    'absent',
                ]),
            ],

            'assessment' => [
                'nullable',
                Rule::in([
                    'normal',
                    'follow_up',
                    'critical',
                ]),
                'required_if:attendance_status,present',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'attendance_status.required' => 'The attendance status field is required.',
            'attendance_status.in'       => 'The selected attendance status is invalid (must be present or absent).',
            
            'assessment.in'              => 'The selected assessment type is invalid.',
            'assessment.required_if'     => 'The assessment field is required when the student is present.',
            
            'notes.string'               => 'The notes must be a string.',
        ];
    }
}
