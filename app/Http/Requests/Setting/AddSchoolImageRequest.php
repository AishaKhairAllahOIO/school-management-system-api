<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class AddSchoolImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*.file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp'],
            'images.*.name' => ['required', 'string', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            'images.required'       => 'At least one image is required.',
            'images.array'          => 'The images must be provided as an array.',
            'images.min'            => 'The images list must contain at least 1 item.',
            
            'images.*.file.required'=> 'Each image file is required.',
            'images.*.file.image'   => 'The uploaded file must be an image.',
            'images.*.file.mimes'   => 'The image must be a file of type: jpeg, png, jpg, webp.',
            
            'images.*.name.required' => 'Each image must have a name.',
            'images.*.name.string'  => 'The image name must be a string.',
            'images.*.name.max'     => 'The image name must not exceed 100 characters.',
        ];
    }
}
