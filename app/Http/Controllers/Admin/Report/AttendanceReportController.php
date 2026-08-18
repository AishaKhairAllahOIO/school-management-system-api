<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\Report\StudentAttendanceManagementReportResource;
use App\Http\Resources\Report\StaffAttendanceManagementReportResource;
use App\Services\Report\AttendanceReportService;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Throwable;

class AttendanceReportController extends Controller
{
    use ApiResource;

    protected AttendanceReportService $service;

    public function __construct(AttendanceReportService $service)
    {
        $this->service = $service;
    }

    /**
     * تقرير غياب وحضور الطلاب المجمل للمدرسة
     */
    public function getStudentAttendanceReport(): JsonResponse
    {
        try {
            $data = $this->service->getOverallStudentsAttendanceReport();
            return $this->successResponse(
                new StudentAttendanceManagementReportResource($data),
                'Student attendance report retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * تقرير غياب وحضور الموظفين والمعلمين المجمل للمدرسة
     */
    public function getStaffAttendanceReport(): JsonResponse
    {
        try {
            $data = $this->service->getOverallStaffAttendanceReport();
            return $this->successResponse(
                new StaffAttendanceManagementReportResource($data),
                'Staff attendance report retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}