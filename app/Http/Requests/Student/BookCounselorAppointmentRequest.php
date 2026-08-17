<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class BookCounselorAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:tomorrow',
                'before_or_equal:tomorrow',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
        ];
    }
}