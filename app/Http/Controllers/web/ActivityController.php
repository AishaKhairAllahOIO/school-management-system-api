<?php

namespace App\Http\Controllers\web;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardianViewActivitiesRequest;
use App\Http\Requests\Web\CreateActivitiesRequest;
use App\Http\Requests\Web\UpdateActivityRequest;
use App\Http\Resources\Web\ActivityResource;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Student;
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
    public function show(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('هذا الحساب ليس حساب طالب.', 403);
        }

        $activities = $this->activityService->showActivities($student);

        return $this->paginatedResponse(
            ActivityResource::collection($activities),
            'تم جلب الأنشطة بنجاح',
            200
        );
    }
    public function guardianViewActivities(GuardianViewActivitiesRequest $request)
    {
        $guardian = $request->user()->guardian;

        if (!$guardian) {
            return $this->errorResponse('guardian not found', 404, null);
        }

        $student = $guardian->students()->where('students.id', $request->student_id)->first();

        if (!$student) {
            return $this->errorResponse('الطالب غير موجود أو غير مرتبط بولي الأمر.', 404, null);
        }

        $activities = $this->activityService->showActivities($student);

        return $this->paginatedResponse(
            ActivityResource::collection($activities),
            'student activities',
            200
        );
    }
    public function destroy(Request $request)
    {
        $this->activityService->deleteActivity($request->id);

        return $this->successResponse(
            null,
            'تم حذف النشاط بنجاح.',
            200
        );
    }

    public function getUnreadCount(Request $request)
    {
        $count = $this->activityService->unreadCount($request->user(), $request->student_id);
        return $this->successResponse(['count' => $count], 'success', 200);
    }

    public function markAllAsRead(Request $request)
    {
        $this->activityService->markAllAsRead($request->user(), $request->student_id);
        return $this->successResponse(null, 'تم تصفير عداد الأنشطة', 200);
    }

    public function showAllActivity(Request $request)
    {
        // $user = $request->user();
        // if (!$user->hasRole('advisor') || $user->hasRole('super_admin')) {
        //     return $this->errorResponse('Not allowed to access here', 403, null);
        // }

        $activities = $this->activityService->getAllActivities();

        return $this->paginatedResponse(
            ActivityResource::collection($activities),
            'those are the activities.',
            200
        );
    }

    public function updateActivity(UpdateActivityRequest $updateActivityRequest, int $id)
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return $this->errorResponse('Activity not found', 404, null);
        }

        $updatedActivity = $this->activityService->updateActivity(
            $activity,
            $updateActivityRequest->validated()
        );
        return $this->successResponse(
            new ActivityResource($updatedActivity),
            'Activity updated successfully',
            200
        );
    }

    public function showActivity(Request $request, int $id)
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return $this->errorResponse('Activity not found', 404, null);
        }

        return $this->successResponse(new ActivityResource($activity),
        'activity shown successfuly',
        200);
    }
}
