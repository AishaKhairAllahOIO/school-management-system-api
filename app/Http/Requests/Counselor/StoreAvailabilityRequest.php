<?php

namespace App\Http\Requests\Counselor;

use App\Enums\SchoolDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvailabilityRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'schedule' => [
                'required',
                'array'
            ],


            'schedule.*.day' => [
                'required',
                'string',
                Rule::enum(SchoolDay::class)
            ],


            'schedule.*.start_time' => [
                'required',
                'date_format:H:i'
            ],


            'schedule.*.end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time'
            ],


            'schedule.*.session_duration' => [
                'required',
                'integer',
                'min:10'
            ],


            'schedule.*.daily_sessions_limit' => [
                'required',
                'integer',
                'min:1'
            ],

        ];
    }
    public function messages(): array
    {
        return [
            'schedule.required'                     => 'The schedule field is required.',
            'schedule.array'                        => 'The schedule must be an array of days.',
            
            'schedule.*.day.required'               => 'The day field is required for all schedule items.',
            'schedule.*.day.string'                 => 'The day must be a valid string.',
            
            'schedule.*.start_time.required'        => 'The start time is required for all schedule items.',
            'schedule.*.start_time.date_format'     => 'The start time must match the format HH:MM.',
            
            'schedule.*.end_time.required'          => 'The end time is required for all schedule items.',
            'schedule.*.end_time.date_format'       => 'The end time must match the format HH:MM.',
            'schedule.*.end_time.after'             => 'The end time must be after the start time.',
            
            'schedule.*.session_duration.required'  => 'The session duration is required.',
            'schedule.*.session_duration.integer'   => 'The session duration must be an integer.',
            'schedule.*.session_duration.min'       => 'The minimum session duration is 10 minutes.',
            
            'schedule.*.daily_sessions_limit.required' => 'The daily sessions limit is required.',
            'schedule.*.daily_sessions_limit.integer'  => 'The daily sessions limit must be an integer.',
            'schedule.*.daily_sessions_limit.min'   => 'The daily sessions limit must be at least 1.',
        ];
    }
}