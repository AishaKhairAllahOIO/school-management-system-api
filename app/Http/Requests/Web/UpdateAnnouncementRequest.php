<?php

namespace App\Http\Requests\Web;

use App\ApiResource;
use App\Models\Announcement;
use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAnnouncementRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        $announcement = Announcement::find($this->route('id'));

        if (!$announcement) {
            throw new HttpResponseException(
                $this->errorResponse('The requested announcement was not found.', 404)
            );
        }

        return $this->user()->can('update', [
            $announcement,
            $this->input('audience') ?? $announcement->audience,
            $this->has('grade_level_id') ? (int) $this->input('grade_level_id') : $announcement->grade_level_id,
            $this->input('class_room_ids')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('You are not authorized to update this announcement.', 403)
        );
    }

    public function rules(): array
    {
        return [
            'audience'         => ['sometimes', Rule::in([
                Announcement::AUDIENCE_STUDENT,
                Announcement::AUDIENCE_STAFF,
                Announcement::AUDIENCE_BOTH,
            ])],
            'title'            => ['sometimes', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'grade_level_id'   => ['nullable', 'integer', 'exists:grade_levels,id'],
            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classRoomIds = $this->input('class_room_ids');
            if (!$classRoomIds || !is_array($classRoomIds)) return;

            $announcement = Announcement::find($this->route('id'));
            $gradeLevelId = $this->input('grade_level_id') ?? ($announcement ? $announcement->grade_level_id : null);

            if ($gradeLevelId) {
                $validRoomsCount = ClassRoom::whereIn('id', $classRoomIds)
                    ->where('grade_level_id', $gradeLevelId)
                    ->count();

                if ($validRoomsCount !== count($classRoomIds)) {
                    $validator->errors()->add(
                        'class_room_ids',
                        'The selected class_room_ids must belong to the specified grade_level_id.'
                    );
                }
            } else {
                $validator->errors()->add(
                    'class_room_ids',
                    'You cannot select class rooms without a specified grade level.'
                );
            }
        });
    }
    public function messages(): array
    {
        return [
            'audience.in'               => 'The selected audience is invalid.',
            
            'title.string'              => 'The announcement title must be a string.',
            'title.max'                 => 'The announcement title must not exceed 255 characters.',
            
            'description.string'        => 'The description must be a string.',
            'description.max'           => 'The description must not exceed 2000 characters.',
            
            'grade_level_id.integer'    => 'The grade level ID must be an integer.',
            'grade_level_id.exists'     => 'The selected grade level does not exist.',
            
            'class_room_ids.array'      => 'Classroom IDs must be provided as an array.',
            'class_room_ids.*.integer'  => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'   => 'One or more selected classrooms do not exist.',
        ];
    }
}
