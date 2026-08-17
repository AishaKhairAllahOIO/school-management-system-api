<?php

namespace App\Http\Requests\Counselor;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCounselorAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'appointment_ids' => [
                'required',
                'array',
            ],

            'appointment_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ];
    }
}