<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolImageRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

 
    public function rules(): array
    {
        return [
            'url' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp'],
            'name' => ['sometimes', 'string', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            'url.image'   => 'The uploaded file must be an image.',
            'url.mimes'   => 'The image must be a file of type: jpeg, png, jpg, webp.',
            
            'name.string' => 'The school image name must be a string.',
            'name.max'    => 'The school image name must not exceed 100 characters.',
        ];
    }
}
