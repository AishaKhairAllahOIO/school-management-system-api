<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\AlertRequest;
use App\Http\Resources\User\AlertResource;
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

        if(!$guardian)
            return $this->errorResponse('this account not for a parent',403,null);


        $student = $guardian->students()->findOrFail($studentId);

        $alerts = $this->alertService->studentAlerts($student);

        return $this->successResponse(
            AlertResource::collection($alerts),
            'تنبيهات الطالب',
            200
        );
    }
    public function childPaymentAlerts(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;

        if(!$guardian)
            return $this->errorResponse('this account not for a parent',403,null);


        $student = $guardian->students()->findOrFail($studentId);

        $alerts = $this->alertService->studentPaymentAlerts($student);

        return $this->successResponse(
            AlertResource::collection($alerts),
            'التنبيهات المالية للطالب',
            200
        );
    }


    public function myAlerts(Request $request)
    {
        $student = $request->user()->student;
        if(!$student)
            return $this->errorResponse('this account not for a student',403,null);

        // جلب التنبيهات الخاصة بالطالب عبر نفس الخدمة المشتركة
        $alerts = $this->alertService->studentAlerts($student);

        return $this->successResponse(
            AlertResource::collection($alerts),
            'تنبيهاتي الشخصية',
            200
        );
    }
    public function getStaffAlerts(Request $request)
    {
        $staff = $request->user()->staff;
        if(!$staff)
            return $this->errorResponse('this account not for a staff',403,null);

        $alerts = $this->alertService->staffAlerts($staff);

        return $this->successResponse(
            AlertResource::collection($alerts),
            'تنبيهاتي الشخصية',
            200
        );
    }
    public function destroy(int $id)
    {
        $this->alertService->deleteAlert($id);

        return $this->successResponse(null, 'تم حذف التنبيه بنجاح.', 200);
    }
    public function store(AlertRequest $request)
    {
        $alert = $this->alertService->createManual($request->validated());

        return $this->successResponse(
            new AlertResource($alert),
            'تم إنشاء التنبيه بنجاح.',
            201
        );
    }
}
