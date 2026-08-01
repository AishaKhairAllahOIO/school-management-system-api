<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'digits:10','starts_with:09'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.digits' => 'The phone number must be exactly 10 digits.',
            'phone_number.starts_with' => 'The phone number must start with 09.',
        ];
    }
}
