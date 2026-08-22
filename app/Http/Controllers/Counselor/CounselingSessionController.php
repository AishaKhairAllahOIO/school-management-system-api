<?php

namespace App\Http\Controllers\Counselor;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Counselor\UpdateCounselingSessionRequest;
use App\Http\Resources\Counselor\CounselingSessionResource;
use App\Services\Counselor\CounselingSessionService;
use Exception;
use Illuminate\Http\Request;

class CounselingSessionController extends Controller
{
    use ApiResource;

    public function __construct(
        private CounselingSessionService $service
    ) {
    }

    public function pending(Request $request)
    {
        try {

            $sessions = $this->service->getPendingSessions(
                $request->user()->id
            );

            return $this->successResponse(
                CounselingSessionResource::collection($sessions),
                'Pending counseling sessions retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

    
    public function update(UpdateCounselingSessionRequest $request, int $sessionId)
    {
        try {

            $session = $this->service->updateSession(
                $sessionId,
                $request->user()->id,
                $request->validated()
            );

            return $this->successResponse(
                new CounselingSessionResource($session),
                'Counseling session updated successfully.',
                200
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }

}
