<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\Report\StudentFinanceReportResource;
use App\Http\Resources\Report\StaffFinanceReportResource;
use App\Services\Report\FinanceReportService;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Throwable;

class FinanceReportController extends Controller
{
    use ApiResource;

    protected FinanceReportService $service;

    public function __construct(FinanceReportService $service)
    {
        $this->service = $service;
    }

    /**
     * تقرير المالية الكلي للطلاب (الإيرادات)
     */
    public function getStudentFinanceReport(): JsonResponse
    {
        try {
            $data = $this->service->getOverallStudentsFinanceReport();
            return $this->successResponse(
                new StudentFinanceReportResource($data),
                'Student finance report retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * تقرير المالية الكلي للموظفين (المصروفات)
     */
    public function getStaffFinanceReport(): JsonResponse
    {
        try {
            $data = $this->service->getOverallStaffFinanceReport();
            return $this->successResponse(
                new StaffFinanceReportResource($data),
                'Staff finance report retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
