<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserVerifyRequest extends FormRequest
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
            'phone_number' => ['required', 'starts_with:09', 'size:10'],
            'otp' => ['required', 'string', 'size:5']
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.starts_with' => 'The phone number must start with 09.',
            'phone_number.size' => 'The phone number must be exactly 10 digits.',
            'otp.required' => 'The OTP field is required.',
            'otp.string' => 'The OTP must be a string.',
            'otp.size' => 'The OTP must be exactly 5 characters.',
        ];
    }
}
