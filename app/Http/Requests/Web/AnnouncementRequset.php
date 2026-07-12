<?php

namespace App\Http\Requests\Web;

use App\Models\Announcement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequset extends FormRequest
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
            'audience'       => ['required', Rule::in([
                Announcement::AUDIENCE_STUDENT,
                Announcement::AUDIENCE_STAFF,
                Announcement::AUDIENCE_BOTH,
            ])],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'grade_level_id' => ['nullable', 'integer', 'exists:grade_levels,id'],
            'class_room_id'  => ['nullable', 'integer', 'exists:class_rooms,id'],
        ];
    }
}
