<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SystemAccessVerifyPasswordRequest extends FormRequest
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
            'email'=>'required|email',
            'otp'=>'required|otp|size:6'
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email'    => 'Please enter a valid email address.',
            'otp.required'   => 'The OTP code field is required.',
            'otp.size'       => 'The OTP code must be exactly 6 characters.',
        ];
    }
}
