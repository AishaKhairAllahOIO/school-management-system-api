<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SystemAccessLoginRequest extends FormRequest
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
            'email'=> 'required|string',
            'password'=> 'required|string',
            'remember_me'=> 'nullable|boolean',
        ];
    }
    public function messages(){
     return [
            'email.required'    => 'البريد الإلكتروني مطلوب لدخول النظام.',
            'email.email'       => 'يرجى إدخال بريد إلكتروني صحيح.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min'      => 'كلمة المرور يجب ألا تقل عن 8 محارف.',
        ];
    }
}
