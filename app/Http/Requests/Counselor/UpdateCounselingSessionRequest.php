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
}
