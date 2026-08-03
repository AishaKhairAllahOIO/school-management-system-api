<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateAlertRequest;
use App\Http\Resources\User\AlertResource;
use App\Services\User\AlertService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use InvalidArgumentException;
use Exception;

class SentAlertController extends Controller
{
    use ApiResource;

    public function __construct(protected AlertService $alertService)
    {
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $alerts = $this->alertService->getAlertsCreatedByUser($request->user(), $perPage);

            $alerts->through(fn($alert) => new AlertResource($alert));

            return $this->paginatedResponse(
                $alerts,
                'Creadted alerts retrieving successfully.',
                200
            );

        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse(
                'An error occurred while retrieving created alerts. ',
                $code,
                ['error' => $e->getMessage()]
            );
        }
    }


    public function update(UpdateAlertRequest $request, $id)
    {
        try {
            $alert = $this->alertService->updateAlert((int) $id, $request->validated(), $request->user());

            return $this->successResponse(
                new AlertResource($alert),
                'The alert updated successfully.',
                200
            );

        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse(
                'An error occurred while updating the alert.',
                $code,
                ['error' => $e->getMessage()]
            );
        }
    }
}
