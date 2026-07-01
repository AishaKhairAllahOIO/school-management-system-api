<?php

namespace App\Services\Web;

use App\ApiResource;
use App\Http\Requests\Web\ActivityRequest;
use App\Models\Activity;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActivityService
{

    use ApiResource;

    public function addActivity(array $data)
    {

        $activity = Activity::create($data);

        return $activity->load(['gradeLevel:id,grade_name', 'classRoom:id,name']);
    }

    public function showActivites(Student $student): Collection
    {

        $currentEnrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->first(['id', 'grade_level_id', 'class_room_id']);

        if (!$currentEnrollment) {
            throw new HttpResponseException($this->errorResponse('لا يوجد تسجيل أكاديمي نشط للطالب في السنة الدراسية الحالية.', 404));
        }

        return Activity::query()
            ->where('grade_level_id', $currentEnrollment->grade_level_id)
            ->where(function ($query) use ($currentEnrollment) {
                $query->whereNull('class_room_id')
                    ->orWhere('class_room_id', $currentEnrollment->class_room_id);
            })
            ->with(['gradeLevel:id,grade_name', 'classRoom:id,name'])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->get();
    }


    public function updateActivity(Activity $activity, array $data)
    {
        $activity->update($data);

        return $activity->load(['gradeLevel:id,grade_name', 'classRoom:id,name']);
    }


    public function deleteActivity(Activity $activity)
    {
        $activity->delete();
    }


}
