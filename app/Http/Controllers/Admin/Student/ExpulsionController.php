<?php

namespace App\Http\Controllers\Admin\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PendingExpulsionResource;
use App\Services\User\AlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ExpulsionController extends Controller
{
    use ApiResource;

    public function __construct(protected AlertService $alertService)
    {
    }


  public function getPending(): JsonResponse
    {
        try {
            $candidates = $this->alertService->getPendingExpulsions();

            return $this->paginatedResponse(
                PendingExpulsionResource::collection($candidates), 
                'Pending expulsion list retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'An error occurred while retrieving the list.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }


    public function confirm(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'enrollment_ids' => 'required|array',
                'enrollment_ids.*' => 'integer|exists:enrollments,id',
            ]);

            $result = $this->alertService->executeConfirmedExpulsions($validated['enrollment_ids']);

            return $this->successResponse(
                ['disabled_accounts_count' => $result['count']],
                "Action completed successfully. {$result['count']} account(s) have been disabled.",
                200
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'An error occurred while disabling accounts.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
