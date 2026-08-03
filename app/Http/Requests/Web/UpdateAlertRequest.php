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
}
