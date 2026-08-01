<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_name' => ['required', 'string', 'max:255', 'unique:subjects,subject_name'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_name.required' => 'The subject name is required.',
            'subject_name.string' => 'The subject name must be a string.',
            'subject_name.max' => 'The subject name may not be greater than 255 characters.',
            'subject_name.unique' => 'The subject name has already been taken.',
        ];
    }
}
