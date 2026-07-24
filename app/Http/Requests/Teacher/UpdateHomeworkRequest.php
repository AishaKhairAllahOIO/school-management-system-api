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
            $this->errorResponse('غير مصرح لك بتعديل هذه الوظيفة، قد تكون الشعبة أو المادة ليست ضمن نطاق صلاحياتك التدريسية.', 403)
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
}
