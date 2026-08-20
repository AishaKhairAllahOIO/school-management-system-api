<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeworkAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                   => ['nullable', 'string', 'max:255'],
            'description'             => ['nullable', 'string', 'max:1000'],
            'add_enrollment_ids'      => ['nullable', 'array'],
            'add_enrollment_ids.*'    => ['integer', 'exists:enrollments,id'],
            'remove_enrollment_ids'   => ['nullable', 'array'],
            'remove_enrollment_ids.*' => ['integer', 'exists:enrollments,id'],
        ];
    }
}