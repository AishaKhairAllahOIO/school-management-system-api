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

    public function messages(): array
    {
        return [
            'first_name.string'     => 'The first name must be a string.',
            'first_name.max'        => 'The first name must not exceed 50 characters.',
            'last_name.string'      => 'The last name must be a string.',
            'phone_number.max'      => 'The phone number must not exceed 20 characters.',
            'national_id.unique'    => 'The national ID is already in use.',
            'birth_date.date'       => 'The birth date is not a valid date.',
            'birth_date.before'     => 'The birth date must be in the past.',
            'gender.in'             => 'The gender must be either male or female.',
        ];
    }
}
