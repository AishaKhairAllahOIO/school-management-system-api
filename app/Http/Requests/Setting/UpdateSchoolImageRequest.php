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
}
