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
            'images'        => ['required', 'array', 'min:1'], // يجب إرسال صورة واحدة على الأقل
            'images.*.file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp'], // فحص الملف المرفوع (5 ميجا كحد أقصى)
            'images.*.name' => ['required', 'string', 'max:100'], // فحص كل اسم داخل المصفوفة
        ];
    }
}