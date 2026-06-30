<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class ImportExalSheetStudentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('student:create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
public function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx,csv'], 
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'يرجى إرفاق ملف الإكسل المراد استيراده',
            'excel_file.mimes'    => 'صيغة الملف يجب أن تكون xlsx أو csv حصراً',
        ];
    }
}
