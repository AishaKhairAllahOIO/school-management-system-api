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
    public function messages(): array
    {
        return [
            'appointment_date.required'       => 'The appointment date field is required.',
            'appointment_date.date'           => 'The appointment date must be a valid date.',
            'appointment_date.after_or_equal' => 'The appointment date must be tomorrow or later.',
            'appointment_date.before_or_equal' => 'The appointment date cannot be after tomorrow.',
            
            'start_time.required'             => 'The start time field is required.',
            'start_time.date_format'          => 'The start time must match the format HH:MM.',
            
            'end_time.required'               => 'The end time field is required.',
            'end_time.date_format'            => 'The end time must match the format HH:MM.',
            'end_time.after'                  => 'The end time must be after the start time.',
        ];
    }
}