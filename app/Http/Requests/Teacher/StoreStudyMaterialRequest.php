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
            'grade_subject_id.required' => 'The grade subject ID field is required.',
            'grade_subject_id.exists'   => 'The selected grade subject does not exist.',
            'grade_level_id.required'   => 'The grade level ID field is required.',
            'grade_level_id.integer'    => 'The grade level ID must be an integer.',
            'grade_level_id.exists'     => 'The selected grade level does not exist.',
            'title.required'            => 'The study material title field is required.',
            'title.string'              => 'The study material title must be a string.',
            'title.max'                 => 'The study material title must not exceed 255 characters.',
            'description.string'        => 'The description must be a string.',
            'type.required'             => 'The material type field is required.',
            'type.in'                   => 'The material type must be either file or link.',
            'file.required_if'          => 'The file is required when the type is set to file.',
            'file.file'                 => 'The uploaded item must be a valid file.',
            'file.mimes'                => 'The file must be of type: pdf, jpg, jpeg, png, pptx, zip, docx, doc.',
            'file.max'                  => 'The file size must not exceed 20MB.',
            'link_url.required_if'      => 'The link URL is required when the type is set to link.',
            'link_url.url'              => 'Please enter a valid URL.',
            'link_url.max'              => 'The link URL must not exceed 2048 characters.',
        ];
    }
}
