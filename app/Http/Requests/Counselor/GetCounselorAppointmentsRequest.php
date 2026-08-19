<?php

namespace App\Http\Requests\Counselor;

use Illuminate\Foundation\Http\FormRequest;

class GetCounselorAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'date.date'           => 'The date must be a valid date.',
            'date.after_or_equal' => 'The date must be today or a future date.',
        ];
    }
}