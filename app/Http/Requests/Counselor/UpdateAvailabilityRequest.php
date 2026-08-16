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
                'required',
                'date_format:H:i'
            ],


            'end_time'=>[
                'required',
                'date_format:H:i',
                'after:start_time'
            ],


            'session_duration'=>[
                'required',
                'integer',
                'min:10'
            ],


            'daily_sessions_limit'=>[
                'required',
                'integer',
                'min:1'
            ],


        ];

    }
}