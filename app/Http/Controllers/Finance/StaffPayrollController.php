<?php

namespace App\Http\Controllers\Finance;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\Staff\PayrollService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPayrollController extends Controller
{
    use ApiResource;
    
    public function __construct(private PayrollService $payrollService) {}


public function myPayrolls(Request $request): JsonResponse
{
    try {

        $staffId = $request->user()
            ->staff
            ->id;


        $payrolls = $this->payrollService
            ->getMyPayrollHistory($staffId);


        return $this->successResponse(
            $payrolls,
            'Payroll history retrieved successfully.',
            200
        );


    } catch (Exception $e) {

        return $this->errorResponse(
            'Failed to retrieve payroll history.',
            500
        );
    }
}
}
