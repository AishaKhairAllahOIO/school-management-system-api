<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralPersonalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'phone_number'  => ['sometimes', 'string', 'max:20', 'different:guardian.phone_number', 'unique:users,phone_number'],
            'first_name'    => ['sometimes', 'string', 'max:50'],
            'last_name'     => ['sometimes', 'string', 'max:50'],
            'father_name'   => ['sometimes', 'string', 'max:50'],
            'mother_name'   => ['sometimes', 'string', 'max:50'],
            'birth_date'    => ['sometimes', 'date', 'before:today'],
            'birth_place'   => ['sometimes', 'string', 'max:100'],
            'address'       => ['sometimes', 'string', 'max:255'],
            'gender'        => ['sometimes', 'in:male,female'],
            'email'        => ['nullable', 'email', 'unique:users,email'],
            'photo_url'     => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp'], 

            ];
    }

    /**
     * تخصيص رسائل الخطأ باللغة العربية
     */
    public function messages(): array
    {
        return [
            'first_name.string'     => 'الاسم الأول يجب أن يكون نصاً.',
            'first_name.max'        => 'الاسم الأول يجب ألا يتجاوز 100 حرف.',
            'last_name.string'      => 'الكنية يجب أن تكون نصاً.',
            'phone_number.max'      => 'رقم الهاتف يجب ألا يتجاوز 20 رقماً.',
            'national_id.unique'    => 'الرقم الوطني مسجل مسبقاً لطالب آخر.',
            'birth_date.date'       => 'صيغة تاريخ الميلاد غير صحيحة.',
            'birth_date.before'     => 'تاريخ الميلاد يجب أن يكون قبل تاريخ اليوم.',
            'gender.in'             => 'الجنس يجب أن يكون ذكراً أو أنثى فقط.',
        ];
    }
}
