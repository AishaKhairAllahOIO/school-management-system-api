<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassStudentEvaluationRequest;
use App\Http\Requests\Teacher\UpdateClassStudentEvaluationRequest;
use App\Http\Resources\Teacher\ClassStudentEvaluationResource;
use App\Models\ClassStudentEvaluation;
use App\Services\Teacher\ClassStudentEvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use InvalidArgumentException;
use Exception;

class ClassStudentEvaluationController extends Controller
{
    use AuthorizesRequests;
    use ApiResource;

    public function __construct(protected ClassStudentEvaluationService $evaluationService)
    {
    }

    private function getAuthStaff(Request $request)
    {
        $user = $request->user();
        if (!$user->staff) {
            throw new InvalidArgumentException('Your account has no linked staff profile to manage school evaluations.', 403);
        }
        return $user->staff;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $this->getAuthStaff($request);
            $evaluations = $this->evaluationService->getTeacherEvaluations($request->user());
            $evaluations->through(fn($evaluation) => new ClassStudentEvaluationResource($evaluation));
            return $this->paginatedResponse(
                $evaluations,
                'Student evaluations list retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function store(StoreClassStudentEvaluationRequest $request): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->createEvaluation(
                $request->user(),
                $request->validated()
            );

            return $this->successResponse(
                new ClassStudentEvaluationResource($evaluation),
                'Student evaluation saved and notification sent successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $evaluation = ClassStudentEvaluation::findOrFail($id);
            $this->authorize('view', $evaluation);

            $evaluation->load(['gradeSubject.subject', 'enrollment.student.user', 'enrollment.classRoom', 'teacher.user']);

            return $this->successResponse(
                new ClassStudentEvaluationResource($evaluation),
                'Student evaluation details retrieved successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student evaluation not found.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('You are not authorized to view this evaluation details.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function update(UpdateClassStudentEvaluationRequest $request, $id): JsonResponse
    {
        try {
            $evaluation = ClassStudentEvaluation::findOrFail($id);
            $updatedEvaluation = $this->evaluationService->updateEvaluation($evaluation, $request->validated());

            return $this->successResponse(
                new ClassStudentEvaluationResource($updatedEvaluation),
                'Student evaluation updated and update alert sent successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student evaluation not found.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('You are not authorized to update this evaluation.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $evaluation = ClassStudentEvaluation::findOrFail($id);
            $this->authorize('delete', $evaluation);
            $this->evaluationService->deleteEvaluation($evaluation);

            return $this->successResponse(null, 'Student evaluation deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student evaluation not found.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('You are not authorized to delete this evaluation.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function studentIndex(Request $request): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getStudentEvaluations($request->user());
            $evaluations->through(fn($evaluation) => new ClassStudentEvaluationResource($evaluation));
            return $this->paginatedResponse(
                $evaluations,
                'Your academic evaluations list retrieved successfully.',
                200
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function guardianChildIndex(Request $request, int $id): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getGuardianChildEvaluations($request->user(), $id);
            $evaluations->through(fn($evaluation) => new ClassStudentEvaluationResource($evaluation));
            return $this->paginatedResponse(
                $evaluations,
                'Selected child evaluations list retrieved successfully.',
                200
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->evaluationService->unreadCount($request->user(), $request->input('student_id'));

            return $this->successResponse(['unread_count' => $count], 'Unread evaluations count retrieved successfully.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $this->evaluationService->markAllAsRead($request->user(), $request->input('student_id'));

            return $this->successResponse(null, 'All evaluations marked as read successfully.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }
}