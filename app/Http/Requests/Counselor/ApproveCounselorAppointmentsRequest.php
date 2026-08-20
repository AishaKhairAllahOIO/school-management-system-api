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
    public function messages(): array
    {
        return [
            'date.required'             => 'The date field is required.',
            'date.date'                 => 'The date must be a valid date.',
            'date.after_or_equal'       => 'The date must be today or a future date.',
            
            'appointment_ids.required'  => 'Please select at least one appointment to approve.',
            'appointment_ids.array'     => 'The appointment IDs must be an array.',
            
            'appointment_ids.*.required'=> 'Each appointment ID is required.',
            'appointment_ids.*.integer' => 'Each appointment ID must be an integer.',
            'appointment_ids.*.distinct' => 'Duplicate appointment IDs are not allowed.',
        ];
    }
}