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
            'status.in'          => 'حالة الملف الممررة غير صالحة.',
            'importer_id.exists' => 'الموظف المطلوب الفلترة به غير موجود في السيستم.',
            'per_page.max'       => 'الحد الأقصى المسموح به هو 100 سطر في الصفحة الواحدة.',
        ];
    }
}
