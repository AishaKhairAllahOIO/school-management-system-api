<?php

namespace App\Http\Requests\Counselor;

use App\Enums\SchoolDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateAvailabilityRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

        return [

            'start_time'=>[
                'sometimes',
                'date_format:H:i'
            ],


            'end_time'=>[
                'sometimes',
                'date_format:H:i',
                'after:start_time'
            ],


            'session_duration'=>[
                'sometimes',
                'integer',
                'min:10'
            ],


            'daily_sessions_limit'=>[
                'sometimes',
                'integer',
                'min:1'
            ],


        ];

    }
    public function messages(): array
    {
        return [
            'start_time.date_format'         => 'The start time must match the format HH:MM.',
            
            'end_time.date_format'           => 'The end time must match the format HH:MM.',
            'end_time.after'                 => 'The end time must be a time after the start time.',
            
            'session_duration.integer'       => 'The session duration must be an integer.',
            'session_duration.min'           => 'The minimum session duration is 10 minutes.',
            
            'daily_sessions_limit.integer'   => 'The daily sessions limit must be an integer.',
            'daily_sessions_limit.min'       => 'The daily sessions limit must be at least 1.',
        ];
    }
}
