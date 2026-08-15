<?php

namespace App\Http\Controllers\Finance;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\GuardianFinancialAccountResource;
use App\Services\Finance\GuardianFinanceService;
use Illuminate\Http\Request;


class GuardianFinanceController extends Controller
{

    use ApiResource;

    public function __construct(
        private GuardianFinanceService $financeService
    ) {
    }



    public function childrenFinance(Request $request)
    {

        $guardianId = $request
            ->user()
            ->guardian
            ->id;


        return response()->json([
            'status' => true,
            'data' => $this->financeService
                ->getChildrenFinancialSummary($guardianId)
        ]);

    }



    public function childFinance(
        Request $request,
        int $studentId
    ) {

        $guardianId = $request
            ->user()
            ->guardian
            ->id;

        $account = $this->financeService
            ->getChildFinancialDetails(
                $guardianId,
                $studentId
            );


        return $this->successResponse(
            new GuardianFinancialAccountResource($account),
            'Financial details retrieved successfully.',
            200
        );

    }

}