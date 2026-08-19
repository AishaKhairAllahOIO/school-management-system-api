<?php

namespace App\Http\Requests\Counselor;

use App\Enums\SchoolDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddAvailabilityRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'day' => [
                'required',
                'string',
                Rule::enum(SchoolDay::class)
            ],

            'start_time' => [
                'required',
                'date_format:H:i'
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time'
            ],

            'session_duration' => [
                'required',
                'integer',
                'min:10'
            ],

            'daily_sessions_limit' => [
                'required',
                'integer',
                'min:1'
            ],

        ];
    }
    public function messages(): array
    {
        return [
            'day.required'               => 'The day field is required.',
            'day.string'                 => 'The day must be a valid string.',
            
            'start_time.required'        => 'The start time field is required.',
            'start_time.date_format'     => 'The start time must match the format HH:MM.',
            
            'end_time.required'          => 'The end time field is required.',
            'end_time.date_format'       => 'The end time must match the format HH:MM.',
            'end_time.after'             => 'The end time must be after the start time.',
            
            'session_duration.required'  => 'The session duration field is required.',
            'session_duration.integer'   => 'The session duration must be an integer.',
            'session_duration.min'       => 'The minimum session duration is 10 minutes.',
            
            'daily_sessions_limit.required' => 'The daily sessions limit field is required.',
            'daily_sessions_limit.integer'  => 'The daily sessions limit must be an integer.',
            'daily_sessions_limit.min'   => 'The daily sessions limit must be at least 1.',
        ];
    }
}
