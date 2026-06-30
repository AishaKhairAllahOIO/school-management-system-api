<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fcm_token'    => ['required', 'string', 'max:512'],
        ];
    }
}
