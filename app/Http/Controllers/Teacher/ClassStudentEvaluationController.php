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

    public function __construct(protected ClassStudentEvaluationService $evaluationService) {}

    private function getAuthStaff(Request $request)
    {
        $user = $request->user();
        if (!$user->staff) {
            throw new InvalidArgumentException('حسابك ليس له ملف موظف (Staff Profile) مرتبط لإدارة التقييمات المدرسية.', 403);
        }
        return $user->staff;
    }


    public function index(Request $request): JsonResponse
    {
        try {
            $this->getAuthStaff($request);
            $evaluations = $this->evaluationService->getTeacherEvaluations($request->user());

            return $this->paginatedResponse(
                ClassStudentEvaluationResource::collection($evaluations),
                'تم جلب قائمة التقييمات الدراسية بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب قائمة التقييمات.', 500, ['error' => $e->getMessage()]);
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
                'تم حفظ تقييم الطالب وإرسال الإشعار بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حفظ التقييم.', 500, ['error' => $e->getMessage()]);
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
                'تم جلب تفاصيل التقييم بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('التقييم المطلوب غير موجود.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('غير مصرح لك بعرض تفاصيل هذا التقييم.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب تفاصيل التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateClassStudentEvaluationRequest $request, $id): JsonResponse
    {
        try {
            $evaluation = ClassStudentEvaluation::findOrFail($id);
            $updatedEvaluation = $this->evaluationService->updateEvaluation($evaluation, $request->validated());

            return $this->successResponse(
                new ClassStudentEvaluationResource($updatedEvaluation),
                'تم تعديل تقييم الطالب وإرسال تنبيه بالتحديث بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('التقييم المطلوب غير موجود.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('غير مصرح لك بتعديل هذا التقييم.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $evaluation = ClassStudentEvaluation::findOrFail($id);
            $this->authorize('delete', $evaluation);
            $this->evaluationService->deleteEvaluation($evaluation);

            return $this->successResponse(null, 'تم حذف التقييم الدراسي بنجاح.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('التقييم المطلوب غير موجود.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('غير مصرح لك بحذف هذا التقييم.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function studentIndex(Request $request): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getStudentEvaluations($request->user());

            return $this->paginatedResponse(
                ClassStudentEvaluationResource::collection($evaluations),
                'تم جلب قائمة تقييماتك الدراسية بنجاح.',
                200
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب تقييمات الطالب.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function guardianChildIndex(Request $request, int $studentId): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getGuardianChildEvaluations($request->user(), $studentId);

            return $this->paginatedResponse(
                ClassStudentEvaluationResource::collection($evaluations),
                'تم جلب قائمة تقييمات الابن المحدد بنجاح.',
                200
            );
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب تقييمات الابن.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->evaluationService->unreadCount($request->user(), $request->input('student_id'));

            return $this->successResponse(['unread_count' => $count], 'تم جلب عدد التقييمات غير المقروءة بنجاح.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب العداد.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $this->evaluationService->markAllAsRead($request->user(), $request->input('student_id'));

            return $this->successResponse(null, 'تم تحديد كافة التقييمات كمقروءة بنجاح.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث حالة القراءة.', 500, ['error' => $e->getMessage()]);
        }
    }
}
