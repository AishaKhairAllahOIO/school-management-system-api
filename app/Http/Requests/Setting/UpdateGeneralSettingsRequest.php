<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Http\Requests\BaseRequest;



class UpdateGeneralSettingsRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
        ;
    }

    public function rules(): array
    {
        return [
            'schoolName' => 'nullable|string|max:255',
            'shortName' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'phoneNumber' => 'nullable|string',
            'emergencyPhoneNumber' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp']
        ];
    }
}
