<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{

    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetUser  = $this->route('user');

        if ($currentUser->hasRole('super_admin')) {
            return true;
        }

        if ($currentUser->hasRole('secretary')) {
            return !$targetUser->hasRole('super_admin') && $targetUser->id !== $currentUser->id;
        }

        return false;
    }


    public function rules(): array
    {
        $targetUserId = $this->route('user')?->id;

        return [
            'first_name'       => ['sometimes', 'string', 'max:50'],
            'last_name'        => ['sometimes', 'string', 'max:50'],
            'phone_number'     => ['sometimes', 'string', Rule::unique('users', 'phone_number')->ignore($targetUserId)],
            'account_status'   => ['sometimes', 'in:enabled,disabled'],

            'degree'           => ['sometimes', 'in:diploma,bachelor,master,phd,other'],
            'specialization'   => ['sometimes', 'string', 'max:100'],
            'university'       => ['sometimes', 'string', 'max:150'],
            'graduation_year'  => ['sometimes', 'integer', 'min:1970', 'max:' . date('Y')],
            'experience_years' => ['sometimes', 'integer', 'min:0', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'first_name.string'       => 'The first name must be a string.',
            'first_name.max'          => 'The first name must not exceed 50 characters.',
            
            'last_name.string'        => 'The last name must be a string.',
            'last_name.max'           => 'The last name must not exceed 50 characters.',
            
            'phone_number.string'     => 'The phone number must be a string.',
            'phone_number.unique'     => 'The phone number has already been taken.',
            
            'account_status.in'       => 'The account status must be either enabled or disabled.',
            
            'degree.in'               => 'The selected degree is invalid.',
            
            'specialization.string'   => 'The specialization must be a string.',
            'specialization.max'      => 'The specialization must not exceed 100 characters.',
            
            'university.string'       => 'The university must be a string.',
            'university.max'          => 'The university must not exceed 150 characters.',
            
            'graduation_year.integer' => 'The graduation year must be an integer.',
            'graduation_year.min'     => 'The graduation year cannot be earlier than 1970.',
            'graduation_year.max'     => 'The graduation year cannot be in the future.',
            
            'experience_years.integer'=> 'The experience years must be an integer.',
            'experience_years.min'    => 'The experience years cannot be negative.',
            'experience_years.max'    => 'The experience years must not exceed 50 years.',
        ];
    }
}
