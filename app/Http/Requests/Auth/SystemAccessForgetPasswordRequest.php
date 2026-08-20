<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class SystemAccessForgetPasswordRequest extends FormRequest
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
            'purpose' => [
                'nullable',
                'string',
                Rule::in([
                    'login',
                    'password_reset',
                ]),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'email.required'  => 'The email field is required.',
            'email.email'     => 'Please enter a valid email address.',
            'purpose.string'  => 'The purpose must be a string.',
            'purpose.in'      => 'The selected purpose is invalid. It must be either login or password_reset.',
        ];
    }
}
