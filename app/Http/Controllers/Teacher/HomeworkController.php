<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreHomeworkRequest;
use App\Http\Requests\Teacher\UpdateHomeworkRequest;
use App\Http\Resources\Teacher\HomeworkResource;
use App\Models\Homework;
use App\Services\Teacher\HomeworkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use InvalidArgumentException;
use Exception;

class HomeworkController extends Controller
{
    use AuthorizesRequests;
    use ApiResource;

    public function __construct(protected HomeworkService $homeworkService) {}


    private function getAuthStaff(Request $request)
    {
        $user = $request->user();
        $staff = $user->staff;

        if (!$staff && !$user->hasAnyRole(['super_admin', 'adviser', 'teacher'])) {
            throw new InvalidArgumentException('This account is not registered as a staff member.', 403);
        }

        if (!$staff) {
            throw new InvalidArgumentException("You are not registered as a staff member.", 403);
        }

        return $staff;
    }

    private function getAuthStudent(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            throw new InvalidArgumentException('This account is not registered as a student.', 403);
        }

        return $student;
    }

    private function getGuardianStudent(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;
        if (!$guardian) {
            throw new InvalidArgumentException('This account is not registered as a guardian.', 403);
        }

        $student = $guardian->students()->find($studentId);
        if (!$student) {
            throw new InvalidArgumentException('Student not found or not associated with the current guardian.', 403);
        }

        return $student;
    }


    public function index(Request $request): JsonResponse
    {
        try {
            $this->getAuthStaff($request);
            $homeworks = $this->homeworkService->getTeacherHomeworks($request->user());

           $homeworks->through(fn($homework) => new HomeworkResource($homework));

            return $this->paginatedResponse(
                $homeworks,
                "Homework list retrieved successfully.",
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while fetching the homework list.", 500, ['error' => $e->getMessage()]);
        }
    }


    public function store(StoreHomeworkRequest $request): JsonResponse
    {
        try {
            $homework = $this->homeworkService->createHomework(
                $request->user(),
                $request->validated()
            );

            return $this->successResponse(
                new HomeworkResource($homework),
                'Homework created successfully and notifications sent to students and guardians.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while creating the homework.", 500, ['error' => $e->getMessage()]);
        }
    }


    public function show($id): JsonResponse
    {
        try {
            $homework = Homework::findOrFail($id);
            $this->authorize('view', $homework);

            return $this->successResponse(
                new HomeworkResource($homework->load([
                    'gradeSubject.subject',
                    'gradeSubject.gradeLevel',
                    'classRooms'
                ])),
                "Homework details retrieved successfully.",
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse("Homework not found.", 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse("You are not authorized to view this homework.", 403);
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while fetching the homework details.", 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateHomeworkRequest $request, $id): JsonResponse
    {
        try {
            $homework = Homework::findOrFail($id);
            $updatedHomework = $this->homeworkService->updateHomework($homework, $request->validated());

            return $this->successResponse(
                new HomeworkResource($updatedHomework),
                'Homework updated successfully and notifications sent to students and guardians.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse("Homework not found.", 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse("You are not authorized to update this homework.", 403);
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while updating the homework.", 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $homework = Homework::findOrFail($id);
            $this->authorize('delete', $homework);
            $this->homeworkService->deleteHomework($homework);

            return $this->successResponse(
                null,
                "Homework deleted successfully.",
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse("Homework not found.", 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse("You are not authorized to delete this homework.", 403);
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while deleting the homework.", 500, ['error' => $e->getMessage()]);
        }
    }


    public function studentIndex(Request $request): JsonResponse
    {
        try {
            $this->getAuthStudent($request);
            $homeworks = $this->homeworkService->getStudentHomeworks($request->user());

            $homeworks->through(fn($homework) => new HomeworkResource($homework));

            return $this->paginatedResponse(
                $homeworks,
                "homework list retrieved successfully.",
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse("Error occurred while fetching student homework list.", 500, ['error' => $e->getMessage()]);
        }
    }


    public function guardianChildIndex(Request $request, int $studentId): JsonResponse
    {
        try {
            $this->getGuardianStudent($request, $studentId);
            $homeworks = $this->homeworkService->getGuardianChildHomeworks($request->user(), $studentId);

            $homeworks->through(fn($homework) => new HomeworkResource($homework));

            return $this->paginatedResponse(
                $homeworks,
                'Child homework list retrieved successfully.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error occurred while fetching child homework list.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function studentUnreadCount(Request $request): JsonResponse
    {
        try {
            $this->getAuthStudent($request);
            $count = $this->homeworkService->unreadCount($request->user());

            return $this->successResponse(['unread_count' => $count], "Unread homework count retrieved successfully.");
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while fetching the unread homework count.", 500, ['error' => $e->getMessage()]);
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->homeworkService->unreadCount($request->user(), $request->input('student_id'));

            return $this->successResponse(['unread_count' => $count], 'Unread homework count retrieved successfully.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while fetching the unread homework count.", 500, ['error' => $e->getMessage()]);
        }
    }
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $this->homeworkService->markAllAsRead($request->user(), $request->input('student_id'));

            return $this->successResponse(null, 'All homework have been marked as read successfully.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse("An error occurred while marking homework as read.", 500, ['error' => $e->getMessage()]);
        }
    }
}
