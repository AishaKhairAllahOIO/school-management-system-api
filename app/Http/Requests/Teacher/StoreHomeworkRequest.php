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
    public function messages(): array
    {
        return [
            'grade_subject_id.required' => 'The grade subject ID field is required.',
            'grade_subject_id.integer'  => 'The grade subject ID must be an integer.',
            'grade_subject_id.exists'   => 'The selected grade subject does not exist.',
            'title.required'            => 'The homework title field is required.',
            'title.string'              => 'The homework title must be a string.',
            'title.max'                 => 'The homework title must not exceed 255 characters.',
            'description.string'        => 'The description must be a string.',
            'due_date.required'         => 'The due date field is required.',
            'due_date.date'             => 'The due date must be a valid date.',
            'due_date.after_or_equal'   => 'The due date must be today or a future date.',
            'class_room_ids.required'   => 'At least one classroom must be selected.',
            'class_room_ids.array'      => 'Classrooms must be provided as an array.',
            'class_room_ids.min'        => 'At least one classroom must be selected.',
            'class_room_ids.*.integer'  => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'   => 'One or more selected classrooms do not exist.',
        ];
    }
}
