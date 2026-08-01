<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class AddSchoolImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*.file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp'],
            'images.*.name' => ['required', 'string', 'max:100'],
        ];
    }
}
