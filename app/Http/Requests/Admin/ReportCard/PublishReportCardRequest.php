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
            'semester_id'   => ['required', 'integer', 'exists:semesters,id'],
            'class_room_id' => ['nullable', 'integer', 'exists:class_rooms,id'], // اختياري (لنشر شعبة معينة أو المدرسة كاملة)
            'is_published'  => ['required', 'boolean'],                          // true للنشر، false لإلغاء النشر
        ];
    }
}
