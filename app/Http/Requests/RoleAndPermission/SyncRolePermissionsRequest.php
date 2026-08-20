<?php

namespace App\Http\Requests\RoleAndPermission;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncRolePermissionsRequest extends FormRequest
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
            'permissions'   => 'required|array',
            'permissions.*' => 'required|integer|exists:permissions,id'
        ];
    }
    public function messages(): array
    {
        return [
            'permissions.required'   => 'The permissions field is required.',
            'permissions.array'      => 'The permissions must be provided as an array.',
            
            'permissions.*.required' => 'Each permission item is required.',
            'permissions.*.integer'  => 'Each permission ID must be an integer.',
            'permissions.*.exists'   => 'One or more selected permissions do not exist in the system.',
        ];
    }
}
