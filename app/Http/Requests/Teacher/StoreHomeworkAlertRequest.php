<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomeworkAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:1000',
            ],

            'enrollment_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'enrollment_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:enrollments,id',
            ],
        ];
    }
}
