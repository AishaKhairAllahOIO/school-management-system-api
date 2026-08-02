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
            throw new InvalidArgumentException('هذا الحساب ليس لموظف أو معلم مسجل في النظام.', 403);
        }

        if (!$staff) {
            throw new InvalidArgumentException('حسابك ليس له ملف موظف (Staff Profile) مرتبط لإدارة الوظائف المدرسية.', 403);
        }

        return $staff;
    }

    private function getAuthStudent(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            throw new InvalidArgumentException('هذا الحساب ليس مسجلاً كطالب لعرض الوظائف المدرسية.', 403);
        }

        return $student;
    }

    private function getGuardianStudent(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;
        if (!$guardian) {
            throw new InvalidArgumentException('هذا الحساب ليس لولي أمر مسجل.', 403);
        }

        $student = $guardian->students()->find($studentId);
        if (!$student) {
            throw new InvalidArgumentException('هذا الطالب لا يتبع لولي الأمر الحالي، غير مصرح لك بالوصول لوظائفه.', 403);
        }

        return $student;
    }


    public function index(Request $request): JsonResponse
    {
        try {
            $this->getAuthStaff($request);
            $homeworks = $this->homeworkService->getTeacherHomeworks($request->user());

            return $this->paginatedResponse(
                HomeworkResource::collection($homeworks),
                'تم جلب قائمة الوظائف المدرسية بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب قائمة الوظائف المدرسية.', 500, ['error' => $e->getMessage()]);
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
                'تم إنشاء الوظيفة المدرسية وإرسال الإشعارات للطلاب وأولياء أمورهم بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إنشاء الوظيفة المدرسية.', 500, ['error' => $e->getMessage()]);
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
                'تم جلب تفاصيل الوظيفة المدرسية بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الوظيفة المدرسية المطلوبة غير موجودة.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('غير مصرح لك بعرض تفاصيل هذه الوظيفة المدرسية.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب تفاصيل الوظيفة المدرسية.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateHomeworkRequest $request, $id): JsonResponse
    {
        try {
            $homework = Homework::findOrFail($id);
            $updatedHomework = $this->homeworkService->updateHomework($homework, $request->validated());

            return $this->successResponse(
                new HomeworkResource($updatedHomework),
                'تم تعديل الوظيفة المدرسية بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الوظيفة المدرسية المطلوبة غير موجودة.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('غير مصرح لك بتعديل هذه الوظيفة المدرسية.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل الوظيفة المدرسية.', 500, ['error' => $e->getMessage()]);
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
                'تم حذف الوظيفة المدرسية بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الوظيفة المدرسية المطلوبة غير موجودة.', 404);
        } catch (AuthorizationException | AccessDeniedHttpException $e) {
            return $this->errorResponse('غير مصرح لك بحذف هذه الوظيفة المدرسية.', 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف الوظيفة المدرسية.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function studentIndex(Request $request): JsonResponse
    {
        try {
            $this->getAuthStudent($request);
            $homeworks = $this->homeworkService->getStudentHomeworks($request->user());

            return $this->paginatedResponse(
                HomeworkResource::collection($homeworks),
                'تم جلب قائمة الوظائف المدرسية الخاصة بشعبتك بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب وظائف الطالب.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function guardianChildIndex(Request $request, int $studentId): JsonResponse
    {
        try {
            $this->getGuardianStudent($request, $studentId);
            $homeworks = $this->homeworkService->getGuardianChildHomeworks($request->user(), $studentId);

            return $this->paginatedResponse(
                HomeworkResource::collection($homeworks),
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

            return $this->successResponse(['unread_count' => $count], 'تم جلب عدد الوظائف غير المقروءة بنجاح.');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب العداد.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->homeworkService->unreadCount($request->user(), $request->input('student_id'));

            return $this->successResponse(['unread_count' => $count], 'تم جلب عدد الوظائف غير المقروءة بنجاح.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب العداد.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $this->homeworkService->markAllAsRead($request->user(), $request->input('student_id'));

            return $this->successResponse(null, 'تم تحديد كافة الوظائف كمقروءة بنجاح.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث حالة القراءة.', 500, ['error' => $e->getMessage()]);
        }
    }
}
