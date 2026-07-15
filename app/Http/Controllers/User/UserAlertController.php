<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AlertRequest;
use App\Http\Resources\User\AlertResource;
use App\Models\Enrollment;
use App\Services\User\AlertService;
use Illuminate\Http\Request;

class UserAlertController extends Controller
{
    use ApiResource;

    private AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function childAlerts(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;

        if (!$guardian)
            return $this->errorResponse('this account not for a parent', 403, null);

        $student = $guardian->students()->find($studentId);

        if (!$student)
            return $this->errorResponse('this student not belong to this parent', 403, null);

        $alerts = $this->alertService->showStudentAlerts($student);

        return $this->paginatedResponse(
            AlertResource::collection($alerts),
            'تنبيهات الطالب',
            200
        );
    }



    public function childPaymentAlerts(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;

        if (!$guardian)
            return $this->errorResponse('this account not for a parent', 403, null);


        $student = $guardian->students()->find($studentId);

        if (!$student)
            return $this->errorResponse('this student not belong to this parent', 403, null);

        $alerts = $this->alertService->showStudentPaymentAlerts($student);

        return $this->paginatedResponse(
            AlertResource::collection($alerts),
            'التنبيهات المالية للطالب',
            200
        );
    }
    public function myAlerts(Request $request)
    {
        $student = $request->user()->student;

        if (!$student)
            return $this->errorResponse('this account not for a student', 403, null);

        $alerts = $this->alertService->showStudentAlerts($student);

        return $this->paginatedResponse(
            AlertResource::collection($alerts),
            'تنبيهاتي الشخصية',
            200
        );
    }
    public function getStaffAlerts(Request $request)
    {
        $staff = $request->user()->staff;
        if (!$staff)
            return $this->errorResponse('this account not for a staff', 403, null);

        $alerts = $this->alertService->showStaffAlerts($staff);

        return $this->paginatedResponse(
            AlertResource::collection($alerts),
            'تنبيهاتي الشخصية',
            200
        );
    }
    public function getStaffPaymentAlerts(Request $request)
    {
        $staff = $request->user()->staff;
        if (!$staff)
            return $this->errorResponse('this account not for a staff', 403, null);

        $alerts = $this->alertService->showStaffPaymentAlerts($staff);

        return $this->paginatedResponse(
            AlertResource::collection($alerts),
            'تنبيهاتي المالية',
            200
        );
    }
    public function destroy(int $id)
    {
        $this->alertService->deleteAlert($id);

        return $this->successResponse(null, 'تم حذف التنبيه بنجاح.', 200);
    }

    // all
    public function store(AlertRequest $request)
    {
        $alert = $this->alertService->createManual($request->validated());

        return $this->successResponse(
            new AlertResource($alert),
            'تم إنشاء التنبيه بنجاح.',
            201
        );
    }
    public function advisorCreateAlerts(AlertRequest $request)
    {
        $alert = $this->alertService->advisorAlerts($request->validated());

        return $this->successResponse(
            new AlertResource($alert),
            'تم إنشاء التنبيه بنجاح.',
            201
        );
    }
    public function teacherCreateAlerts(AlertRequest $request)
    {

        $enrollment = Enrollment::findOrFail($request['enrollment_id']);
        $alert = $this->alertService->createStudentHomework($enrollment, $request->validated());

        return $this->successResponse(
            new AlertResource($alert),
            'تم إنشاء التنبيه بنجاح.',
            201
        );
    }
    public function staffAlerts(AlertRequest $request)
    {
        $alert = $this->alertService->createStaffAlerts($request->validated());

        return $this->successResponse(
            new AlertResource($alert),
            'تم إنشاء التنبيه بنجاح.',
            201
        );
    }
    public function paymentAlerts(AlertRequest $request)
    {
        $alert = $this->alertService->createPaymentAlerts($request->validated());
        return $this->successResponse(
            new AlertResource($alert),
            'تم إنشاء التنبيه بنجاح.',
            201
        );
    }

    public function markAllAlertsRead(Request $request)
    {
        $category = $request->query('category', 'all');
        $studentId = $request->input('student_id');

        $counts = $this->alertService->markAllReadForUser($request->user(), $category, $studentId);

        return $this->successResponse(
            $counts,
            'تم تصفير العداد المطلوب.',
            200
        );
    }

 public function unreadAlertsCount(Request $request)
    {
        $studentId = $request->input('student_id');
        $counts = $this->alertService->unreadCountForUser($request->user(), $studentId);

        return $this->successResponse(
            $counts,
            'تم جلب عدد التنبيهات غير المقروءة.',
            200
        );
    }
}
