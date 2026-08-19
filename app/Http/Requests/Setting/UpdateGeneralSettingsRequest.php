<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Http\Requests\BaseRequest;



class UpdateGeneralSettingsRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
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
    public function messages(): array
    {
        return [
            'schoolName.string'           => 'The school name must be a string.',
            'schoolName.max'              => 'The school name must not exceed 255 characters.',
            
            'shortName.string'            => 'The short name must be a string.',
            'shortName.max'               => 'The short name must not exceed 50 characters.',
            
            'description.string'          => 'The description must be a string.',
            
            'phoneNumber.string'          => 'The phone number must be a string.',
            'emergencyPhoneNumber.string' => 'The emergency phone number must be a string.',
            
            'email.email'                 => 'Please enter a valid email address.',
            
            'website.url'                 => 'Please enter a valid URL for the website.',
            
            'address.string'              => 'The address must be a string.',
            'city.string'                 => 'The city must be a string.',
            'country.string'              => 'The country must be a string.',
            
            'location.latitude.numeric'   => 'The latitude must be a valid number.',
            'location.longitude.numeric'  => 'The longitude must be a valid number.',
            
            'logo.image'                  => 'The logo file must be an image.',
            'logo.mimes'                  => 'The logo must be a file of type: jpeg, png, jpg, svg, webp.',
        ];
    }
}
