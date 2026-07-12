<?php

namespace App\Services\Web;

use App\ApiResource;
use App\Http\Requests\Web\ActivityRequest;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActivityService
{

    use ApiResource;

    public function addActivity(array $data)
    {

        $activity = Activity::create($data);

        return $activity->load(['gradeLevel:id,name', 'classRoom:id,name']);
    }

    public function showActivities(Student $student): Collection
    {
         $enrollment = $student->enrollments()
        ->whereHas('academicYear', function ($q) {
            $q->whereDate('start_date', '<=', now())
              ->whereDate('end_date', '>=', now());
        })
        ->latest()
        ->first();

        return Activity::query()
            ->where('grade_level_id', $enrollment->grade_level_id)
            ->where(function ($query) use ($enrollment) {
                $query->whereNull('class_room_id')
                    ->orWhere('class_room_id', $enrollment->class_room_id);
            })
            ->with(['gradeLevel:id,name', 'classRoom:id,name'])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->get();
    }

    public function updateActivity(Activity $activity, array $data)
    {
        $activity->update($data);

        return $activity->load(['gradeLevel:id,name', 'classRoom:id,name']);
    }


    public function deleteActivity(int $id): void
    {
        $activity = Activity::find($id);

        if(!$activity)
            throw new HttpResponseException($this->errorResponse('Activity not found.', 404));
        $activity->delete();
    }



}
