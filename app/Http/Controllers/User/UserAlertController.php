<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AlertRequest;
use App\Http\Resources\User\AlertResource;
use App\Models\Alert;
use App\Models\Enrollment;
use App\Services\User\AlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use InvalidArgumentException;

class UserAlertController extends Controller
{
    use ApiResource;

    public function __construct(protected AlertService $alertService)
    {
    }

    private function getGuardianStudent(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;
        if (!$guardian) {
            throw new InvalidArgumentException('This account does not belong to a guardian.', 403);
        }

        $student = $guardian->students()->find($studentId);
        if (!$student) {
            throw new InvalidArgumentException('This student does not belong to the current guardian.', 403);
        }

        return $student;
    }

    private function getAuthStudent(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            throw new InvalidArgumentException('This account does not belong to a student.', 403);
        }

        return $student;
    }

    private function getAuthStaff(Request $request)
    {
        $user = $request->user();
        $staff = $user->staff;

        if (!$staff && !$user->hasAnyRole(['super_admin', 'adviser', 'teacher'])) {
            throw new InvalidArgumentException('This account does not belong to a registered staff member, teacher, or advisor.', 403);
        }

        if (!$staff) {
            throw new InvalidArgumentException('Your admin/advisor account does not have a linked Staff Profile to view personal alerts.', 403);
        }

        return $staff;
    }

    public function childAlerts(Request $request, int $studentId): JsonResponse
    {
        try {
            $student = $this->getGuardianStudent($request, $studentId);
            $alerts = $this->alertService->showStudentAlerts($student);
            $alerts->through(fn($alert) => new AlertResource($alert));
            return $this->paginatedResponse(
                $alerts,
                'Student alerts retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function childPaymentAlerts(Request $request, int $studentId): JsonResponse
    {
        try {
            $student = $this->getGuardianStudent($request, $studentId);
            $alerts = $this->alertService->showStudentPaymentAlerts($student);
            $alerts->through(fn($alert) => new AlertResource($alert));
            return $this->paginatedResponse(
                $alerts,
                'Student payment alerts retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function myAlerts(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthStudent($request);
            $alerts = $this->alertService->showStudentAlerts($student);
            $alerts->through(fn($alert) => new AlertResource($alert));
            return $this->paginatedResponse(
                $alerts,
                'Personal alerts retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getStaffAlerts(Request $request): JsonResponse
    {
        try {
            $staff = $this->getAuthStaff($request);
            $alerts = $this->alertService->showStaffAlerts($staff);
    $alerts->through(fn($alert) => new AlertResource($alert));
            return $this->paginatedResponse(
                $alerts,
                'Personal alerts retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getStaffPaymentAlerts(Request $request): JsonResponse
    {
        try {
            $staff = $this->getAuthStaff($request);
            $alerts = $this->alertService->showStaffPaymentAlerts($staff);
        $alerts->through(fn($alert) => new AlertResource($alert));

            return $this->paginatedResponse(
                $alerts,
                'Personal payment alerts retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            if (!$request->user()->hasAnyRole(['super_admin', 'adviser', 'teacher', 'secretary'])) {
                return $this->errorResponse('You are not authorized to delete alerts.', 403);
            }

            $this->alertService->deleteAlert($id);

            return $this->successResponse(null, 'Alert deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested alert was not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function store(AlertRequest $request): JsonResponse
    {
        try {
            $alerts = $this->alertService->createManual($request->validated());
            return $this->successResponse(
                AlertResource::collection($alerts),
                'Alerts created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function advisorCreateAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $alerts = $this->alertService->advisorAlerts($request->validated());
            return $this->successResponse(
                AlertResource::collection($alerts),
                'Alerts created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function teacherCreateAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $alerts = $this->alertService->createBatchStudentAlerts(
                $validated['enrollment_ids'],
                Alert::TYPE_HOMEWORK,
                $validated['meta'] ?? [],
                $validated['title'] ?? null,
                $validated['description'] ?? null
            );

            return $this->successResponse(
                AlertResource::collection($alerts),
                'Homework alerts created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function staffAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $alerts = $this->alertService->createStaffAlerts($request->validated());
            return $this->successResponse(
                AlertResource::collection($alerts),
                'Alerts created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function paymentAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $alerts = $this->alertService->createPaymentAlerts($request->validated());
            return $this->successResponse(
                AlertResource::collection($alerts),
                'Alerts created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function markAllAlertsRead(Request $request): JsonResponse
    {
        try {
            $category = $request->query('category', 'all');
            $studentId = $request->input('student_id');

            $counts = $this->alertService->markAllReadForUser($request->user(), $category, $studentId);

            return $this->successResponse(
                $counts,
                'Alerts marked as read successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
               'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function unreadAlertsCount(Request $request): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');
            $counts = $this->alertService->unreadCountForUser($request->user(), $studentId);

            return $this->successResponse(
                $counts,
                'Unread alerts count retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
