<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('subjects', 'subject_name')->ignore($this->route('subject')),
            ],
        ];
    }
}
