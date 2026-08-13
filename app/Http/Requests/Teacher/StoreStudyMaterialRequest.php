<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudyMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => 'required|exists:grade_subjects,id',
            'grade_level_id'   => 'required', 'integer', 'exists:grade_levels,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'type'             => 'required|in:file,link',

            'file'             => 'required_if:type,file|file|mimes:pdf,jpg,jpeg,png,pptx,zip,docx,doc|max:20480',
            'link_url'         => 'required_if:type,link|url|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required_if'     => 'The file is required when the type is set to file.',
            'link_url.required_if' => 'The link URL is required when the type is set to link.',
        ];
    }
}
