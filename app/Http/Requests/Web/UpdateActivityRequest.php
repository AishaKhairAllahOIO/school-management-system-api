<?php

namespace App\Http\Requests\Web;

use App\Models\Activity;
use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateActivityRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'grade_level_id'   => ['sometimes', 'integer', 'exists:grade_levels,id'],

            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],

            'type'             => ['sometimes', 'string', 'max:255'],
            'activity_name'    => ['sometimes', 'string', 'max:255'],
            'activity_date'    => ['sometimes', 'date', 'after:today'],
            'start_time'       => ['sometimes', 'date_format:H:i'],
            'end_time'         => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'description'      => ['sometimes', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            $classRoomIds = $this->input('class_room_ids');

            if (!$classRoomIds || !is_array($classRoomIds)) return;

            $gradeLevelId = $this->input('grade_level_id');

            if (!$gradeLevelId) {
                $activityId = $this->route('id');
                $activity = Activity::find($activityId);

                if ($activity) {
                    $gradeLevelId = $activity->grade_level_id;
                }
            }

            if ($gradeLevelId) {
                $validRoomsCount = ClassRoom::whereIn('id', $classRoomIds)
                    ->where('grade_level_id', $gradeLevelId)
                    ->count();

                if ($validRoomsCount !== count($classRoomIds)) {
                    $validator->errors()->add(
                        'class_room_ids',
                        'إحدى الشعب المختارة أو أكثر لا تتبع المرحلة الدراسية المحددة لهذا النشاط.'
                    );
                }
            }
        });
    }
}
