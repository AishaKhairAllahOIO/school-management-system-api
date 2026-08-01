<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetBatchesHistoryExalFilesRequest extends FormRequest
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
            'status'      => ['nullable', 'string', 'in:pending,processing,completed,failed'],
            'search'      => ['nullable', 'string', 'max:100'],
            'per_page'    => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'          => 'accepted values are: pending, processing, completed, failed.',
            'importer_id.exists' => 'accepted value must exist in the importers table.',
            'per_page.max'       => 'the maximum allowed is 100 rows per page.',
        ];
    }
}
