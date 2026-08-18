<?php

namespace App\Http\Requests\Admin\ReportCard;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PublishReportCardRequest extends FormRequest
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
            'semester_id.required' => 'The semester ID is required.',
            'semester_id.integer'  => 'The semester ID must be an integer.',
            'semester_id.exists'   => 'The selected semester does not exist in the system.',

            'class_room_id.integer' => 'The class room ID must be an integer.',
            'class_room_id.exists'  => 'The selected class room does not exist in the system.',

            'is_published.required' => 'The publish status is required.',
            'is_published.boolean'  => 'The publish status must be a boolean value (true or false).',
        ];
    }
}
