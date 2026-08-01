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
}
