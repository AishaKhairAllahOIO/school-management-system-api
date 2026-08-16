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
}