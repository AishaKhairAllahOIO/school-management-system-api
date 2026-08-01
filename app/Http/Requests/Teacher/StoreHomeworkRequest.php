<?php

namespace App\Http\Requests\Teacher;

use App\ApiResource;
use App\Models\Homework;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHomeworkRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        $gradeSubjectId = (int) $this->input('grade_subject_id', 0);
        $classRoomIds = (array) $this->input('class_room_ids', []);

        return $this->user()->can('create', [
            Homework::class,
            $gradeSubjectId,
            $classRoomIds
        ]);
    }
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('You are not authorized to create a homework.', 403)
        );
    }
    public function rules(): array
    {
        return [
            'grade_subject_id' => ['required', 'integer', 'exists:grade_subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],

            'class_room_ids' => ['required', 'array', 'min:1'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
        ];
    }
}
