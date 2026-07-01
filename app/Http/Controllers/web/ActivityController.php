<?php

namespace App\Http\Controllers\web;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardianViewActivitiesRequest;
use App\Http\Requests\Web\CreateActivitiesRequest;
use App\Http\Resources\Web\ActivityResource;
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
        if (!$student)
            return $this->errorResponse('هذا الحساب غير مرتبط بطالب.', 403, null);


        $activities = $this->activityService->showActivites($student);

        return $this->successResponse(ActivityResource::collection($activities), 'student activites', 200);
    }

    public function guardianViewActivities(GuardianViewActivitiesRequest $request)
    {
        $guardian = $request->user()->guardian;

        abort_if($guardian === null, 403, 'هذا الحساب غير مرتبط بولي أمر.');

        $student = $guardian->students()->findOrFail($request->student_id);

        $activities = $this->activityService->showActivites($student);

        return $this->successResponse(
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
}
