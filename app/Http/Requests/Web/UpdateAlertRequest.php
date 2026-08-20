<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'audience'    => 'sometimes|string|in:student,staff',
            'type'        => 'sometimes|string',
            'meta'        => 'nullable|array',
        ];
    }
    public function messages(): array
    {
        return [
            'title.string'       => 'The alert title must be a string.',
            'title.max'          => 'The alert title must not exceed 255 characters.',
            
            'description.string' => 'The alert description must be a string.',
            
            'audience.string'    => 'The audience must be a string.',
            'audience.in'        => 'The selected audience must be either student or staff.',
            
            'type.string'        => 'The alert type must be a string.',
            
            'meta.array'         => 'The meta data must be structured as an array.',
        ];
    }
}
