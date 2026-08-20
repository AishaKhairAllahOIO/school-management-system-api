<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolLawRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'title.string'       => 'The school law title must be a string.',
            'title.max'          => 'The school law title must not exceed 255 characters.',
            
            'description.string' => 'The description must be a string.',
        ];
    }
}
