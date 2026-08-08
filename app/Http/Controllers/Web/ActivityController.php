<?php

namespace App\Http\Controllers\Web;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardianViewActivitiesRequest;
use App\Http\Requests\Web\CreateActivitiesRequest;
use App\Http\Requests\Web\UpdateActivityRequest;
use App\Http\Resources\Web\ActivityResource;
use App\Models\Activity;
use App\Services\Web\ActivityService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use ApiResource;

    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }


    public function store(CreateActivitiesRequest $activityRequest)
    {
        $result = $this->activityService->addActivity($activityRequest->validated());

        return $this->successResponse(
            new ActivityResource($result),
            'Activity has been added successfully',
            201
        );
    }

    public function updateActivity(UpdateActivityRequest $updateActivityRequest, int $id)
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return $this->errorResponse('Activity not found', 404, null);
        }

        $updatedActivity = $this->activityService->updateActivity($activity, $updateActivityRequest->validated());
        return $this->successResponse(
            new ActivityResource($updatedActivity),
            'Activity updated successfully',
            200
        );
    }

    public function destroy(Request $request)
    {
        $activity = Activity::find($request->id);
        if (!$activity) {
            return $this->errorResponse('Activity not found', 404, null);
        }

        if (!$request->user()->can('delete', $activity)) {
            return $this->errorResponse('Access denied.', 403, null);
        }

        $this->activityService->deleteActivity($activity->id);

        return $this->successResponse(null, 'Activity deleted successfully.', 200);
    }

    public function show(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('student not found', 404, null);
        }
        $activities = $this->activityService->showActivities($student);
        $activities->through(fn($activity) => new ActivityResource($activity));
        return $this->paginatedResponse(
            $activities,
            'student activities',
            200
        );
    }

    public function showAllActivity(Request $request)
    {
        if (!$request->user()->can('viewAny', Activity::class)) {
            return $this->errorResponse('Access denied.', 403, null);
        }

        $activities = $this->activityService->getAllActivities($request->user());
        $activities->through(fn($activity) => new ActivityResource($activity));
        return $this->paginatedResponse(
            $activities,
            'those are the activities.',
            200
        );
    }
    public function guardianViewActivities(GuardianViewActivitiesRequest $request)
    {
        $guardian = $request->user()->guardian;

        if (!$guardian)
            return $this->errorResponse('guardian not found', 404, null);

        $student = $guardian->students()->where('students.id', $request->student_id)->first();

        if (!$student)
            return $this->errorResponse('student not found or not associated with the guardian.', 404, null);

        $activities = $this->activityService->showActivities($student);
        $activities->through(fn($activity) => new ActivityResource($activity));
        return $this->paginatedResponse(
            $activities,
            'student activities',
            200
        );
    }

    public function showActivity(Request $request, int $id)
    {
        $activity = Activity::find($id);
        if (!$activity)
            return $this->errorResponse('Activity not found', 404, null);

        return $this->successResponse(new ActivityResource($activity), 'activity shown successfuly', 200);
    }


    public function getUnreadCount(Request $request)
    {
        $count = $this->activityService->unreadCount($request->user(), $request->student_id);
        return $this->successResponse(['count' => $count], 'success', 200);
    }

    public function markAllAsRead(Request $request)
    {
        $this->activityService->markAllAsRead($request->user(), $request->student_id);
        return $this->successResponse(null, 'Activity read status reset successfully.', 200);
    }
}
