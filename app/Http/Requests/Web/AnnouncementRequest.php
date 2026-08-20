<?php

namespace App\Http\Requests\Web;

use App\ApiResource;
use App\Models\Announcement;
use App\Models\ClassRoom;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{//
  use ApiResource;

    public function authorize(): bool
    {
        return $this->user()->can('create', [
            Announcement::class,
            $this->input('audience'),
            $this->input('grade_level_id') ? (int) $this->input('grade_level_id') : null,
            $this->input('class_room_ids')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('You are not authorized to create this announcement.', 403)
        );
    }
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
            'grade_level_id'   => ['required_if:audience,' . Announcement::AUDIENCE_STUDENT,'nullable', 'integer', 'exists:grade_levels,id'],
            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classRoomIds = $this->input('class_room_ids');

            if (!$classRoomIds || !is_array($classRoomIds)) return;

            $validRoomsCount = ClassRoom::whereIn('id', $classRoomIds)
                ->where('grade_level_id', $this->input('grade_level_id'))
                ->count();

            if ($validRoomsCount !== count($classRoomIds)) {
                $validator->errors()->add(
                    'class_room_ids',
                    'The selected class_room_ids must belong to the specified grade_level_id.'
                );
            }
        });
    }
    public function messages(): array
    {
        return [
            'audience.required'         => 'The audience field is required.',
            'audience.in'               => 'The selected audience is invalid.',
            
            'title.required'            => 'The announcement title field is required.',
            'title.string'              => 'The announcement title must be a string.',
            'title.max'                 => 'The announcement title must not exceed 255 characters.',
            
            'description.string'        => 'The description must be a string.',
            'description.max'           => 'The description must not exceed 2000 characters.',
            
            'grade_level_id.required_if'=> 'The grade level is required when the audience is set to student.',
            'grade_level_id.integer'    => 'The grade level ID must be an integer.',
            'grade_level_id.exists'     => 'The selected grade level does not exist.',
            
            'class_room_ids.array'      => 'Classroom IDs must be provided as an array.',
            'class_room_ids.*.integer'  => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'   => 'One or more selected classrooms do not exist.',
        ];
    }
}
