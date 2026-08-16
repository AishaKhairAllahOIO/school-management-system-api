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
}
