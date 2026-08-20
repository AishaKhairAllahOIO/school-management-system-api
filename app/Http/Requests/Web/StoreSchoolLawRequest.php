<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolLawRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'title.required'       => 'The school law title field is required.',
            'title.string'         => 'The school law title must be a string.',
            'title.max'            => 'The school law title must not exceed 255 characters.',
            
            'description.required' => 'The description field is required.',
            'description.string'   => 'The description must be a string.',
        ];
    }
}
