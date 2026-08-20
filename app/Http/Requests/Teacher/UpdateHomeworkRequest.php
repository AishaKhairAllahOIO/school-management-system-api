<?php

namespace App\Http\Requests\Teacher;

use App\ApiResource;
use App\Models\Homework;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateHomeworkRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        $homework = $this->route('homework');

        if (!$homework instanceof Homework) {
            $id = $this->route('homework') ?? $this->route('id') ?? $this->route('homework_id');
            $homework = Homework::find($id);
        }

        if (!$homework) {
            return false;
        }

        $gradeSubjectId = $this->has('grade_subject_id')
            ? (int) $this->input('grade_subject_id')
            : (int) $homework->grade_subject_id;

        $classRoomIds = $this->has('class_room_ids')
            ? (array) $this->input('class_room_ids')
            : $homework->classRooms()->allRelatedIds()->toArray();

        return $this->user()->can('update', [
            $homework,
            $gradeSubjectId,
            $classRoomIds
        ]);
    }
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('You are not authorized to update this homework.', 403)
        );
    }
    public function rules(): array
    {
        return [
            'grade_subject_id' => ['sometimes', 'required', 'integer', 'exists:grade_subjects,id'],
            'title'            => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'due_date'         => ['sometimes', 'required', 'date', 'after_or_equal:today'],

            'class_room_ids'   => ['sometimes', 'required', 'array', 'min:1'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'grade_subject_id.integer' => 'The grade subject ID must be an integer.',
            'grade_subject_id.exists'  => 'The selected grade subject does not exist.',
            'title.string'             => 'The homework title must be a string.',
            'title.max'                => 'The homework title must not exceed 255 characters.',
            'description.string'       => 'The homework description must be a string.',
            'due_date.date'            => 'The due date must be a valid date.',
            'due_date.after_or_equal'  => 'The due date must be today or a future date.',
            'class_room_ids.array'     => 'The classrooms must be provided as an array.',
            'class_room_ids.min'       => 'At least one classroom must be selected.',
            'class_room_ids.*.integer' => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'  => 'One or more selected classrooms do not exist.',
        ];
    }
}
