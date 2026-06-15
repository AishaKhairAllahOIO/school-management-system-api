<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\BaseRequest;



class UpdateGeneralSettingsRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('school:update_info');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      return [
            'schoolName' => 'required|string|max:255',
            'shortName' => 'required|string|max:50',
            'description' => 'nullable|string',
            'phoneNumber' => 'required|string',
            'emergencyPhoneNumber' => 'nullable|string',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'defaultLanguage' => 'required|string|max:5',
            'timezone' => 'required|string',
            'dateFormat' => 'required|string',
            'currency' => 'required|string',
            'workingDays' => 'required|array',
            'workingDays.*' => 'string',
            'openingTime' => 'required|date_format:H:i',
            'closingTime' => 'required|date_format:H:i',
        ];
    }
}
