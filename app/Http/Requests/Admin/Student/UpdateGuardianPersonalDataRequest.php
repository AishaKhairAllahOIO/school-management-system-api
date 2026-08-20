<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Guardian;
use Illuminate\Validation\Rule;


class UpdateGuardianPersonalDataRequest extends FormRequest
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
       $parentId = $this->route('guardian');

        $parentRecord = Guardian::find($parentId);
        $userId = $parentRecord ? $parentRecord->user_id : null;

        return [
            'first_name'   => ['sometimes', 'string', 'max:50'],
            'last_name'    => ['sometimes', 'string', 'max:50'],
            'father_name'  => ['sometimes', 'string', 'max:50'],
            'mother_name'  => ['sometimes', 'string', 'max:50'],
            'birth_date'   => ['sometimes', 'date'],
            'birth_place'  => ['sometimes', 'string', 'max:100'],
            'address'      => ['sometimes', 'string', 'max:255'],
            'gender'       => ['sometimes', 'in:male,female'],
            'nationality'  => ['sometimes', 'in:syrian,lebanese,palestinian,jordanian,other'],
            'photo_url'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],

            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'first_name.string'   => 'The first name must be a string.',
            'first_name.max'      => 'The first name must not exceed 50 characters.',
            'last_name.string'    => 'The last name must be a string.',
            'last_name.max'       => 'The last name must not exceed 50 characters.',
            'father_name.string'  => 'The father name must be a string.',
            'father_name.max'     => 'The father name must not exceed 50 characters.',
            'mother_name.string'  => 'The mother name must be a string.',
            'mother_name.max'     => 'The mother name must not exceed 50 characters.',
            'birth_date.date'     => 'The birth date must be a valid date.',
            'birth_place.string'  => 'The birth place must be a string.',
            'birth_place.max'     => 'The birth place must not exceed 100 characters.',
            'address.string'      => 'The address must be a string.',
            'address.max'         => 'The address must not exceed 255 characters.',
            'gender.in'           => 'The selected gender is invalid.',
            'nationality.in'      => 'The selected nationality is invalid.',
            'photo_url.image'     => 'The file must be an image.',
            'photo_url.mimes'     => 'The image must be a file of type: jpeg, png, jpg, webp.',
            'phone_number.string' => 'The phone number must be a string.',
            'phone_number.max'    => 'The phone number must not exceed 20 characters.',
            'phone_number.unique' => 'The phone number has already been taken.',
            'email.email'         => 'The email must be a valid email address.',
            'email.unique'        => 'The email has already been taken.',
        ];
    }
}
