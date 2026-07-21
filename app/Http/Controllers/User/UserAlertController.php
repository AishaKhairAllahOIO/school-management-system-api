<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AlertRequest;
use App\Http\Resources\User\AlertResource;
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

    public function __construct(protected AlertService $alertService) {}


    private function getGuardianStudent(Request $request, int $studentId)
    {
        $guardian = $request->user()->guardian;
        if (!$guardian) {
            throw new InvalidArgumentException('هذا الحساب ليس لولي أمر.', 403);
        }

        $student = $guardian->students()->find($studentId);
        if (!$student) {
            throw new InvalidArgumentException('هذا الطالب لا يتبع لولي الأمر الحالي.', 403);
        }

        return $student;
    }

    private function getAuthStudent(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            throw new InvalidArgumentException('هذا الحساب ليس لطالب.', 403);
        }

        return $student;
    }

    private function getAuthStaff(Request $request)
    {
        $staff = $request->user()->staff;
        if (!$staff) {
            throw new InvalidArgumentException('هذا الحساب ليس لموظف أو معلم.', 403);
        }

        return $staff;
    }

    public function childAlerts(Request $request, int $studentId): JsonResponse
    {
        try {
            $student = $this->getGuardianStudent($request, $studentId);
            $alerts = $this->alertService->showStudentAlerts($student);

            return $this->paginatedResponse(
                AlertResource::collection($alerts),
                'تم جلب تنبيهات الطالب بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب التنبيهات.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function childPaymentAlerts(Request $request, int $studentId): JsonResponse
    {
        try {
            $student = $this->getGuardianStudent($request, $studentId);
            $alerts = $this->alertService->showStudentPaymentAlerts($student);

            return $this->paginatedResponse(
                AlertResource::collection($alerts),
                'تم جلب التنبيهات المالية للطالب بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب التنبيهات.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function myAlerts(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthStudent($request);
            $alerts = $this->alertService->showStudentAlerts($student);

            return $this->paginatedResponse(
                AlertResource::collection($alerts),
                'تم جلب تنبيهاتي الشخصية بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب التنبيهات.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getStaffAlerts(Request $request): JsonResponse
    {
        try {
            $staff = $this->getAuthStaff($request);
            $alerts = $this->alertService->showStaffAlerts($staff);

            return $this->paginatedResponse(
                AlertResource::collection($alerts),
                'تم جلب تنبيهاتي الشخصية بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب التنبيهات.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getStaffPaymentAlerts(Request $request): JsonResponse
    {
        try {
            $staff = $this->getAuthStaff($request);
            $alerts = $this->alertService->showStaffPaymentAlerts($staff);

            return $this->paginatedResponse(
                AlertResource::collection($alerts),
                'تم جلب تنبيهاتي المالية بنجاح.',
                200
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب التنبيهات.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->alertService->deleteAlert($id);
            return $this->successResponse(null, 'تم حذف التنبيه بنجاح.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('التنبيه المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف التنبيه.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function store(AlertRequest $request): JsonResponse
    {
        try {
            $alert = $this->alertService->createManual($request->validated());
            return $this->successResponse(
                new AlertResource($alert),
                'تم إنشاء التنبيه بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء التنبيه.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function advisorCreateAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $alert = $this->alertService->advisorAlerts($request->validated());
            return $this->successResponse(
                new AlertResource($alert),
                'تم إنشاء التنبيه بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء التنبيه.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function teacherCreateAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $enrollment = Enrollment::findOrFail($request->validated()['enrollment_id'] ?? null);
            $alert = $this->alertService->createStudentHomework($enrollment, $request->validated());

            return $this->successResponse(
                new AlertResource($alert),
                'تم إنشاء التنبيه بنجاح.',
                201
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'سجل تسجيل الطالب المطلوب غير موجود.',
                404
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء التنبيه.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function staffAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $alert = $this->alertService->createStaffAlerts($request->validated());
            return $this->successResponse(
                new AlertResource($alert),
                'تم إنشاء التنبيه بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء التنبيه.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function paymentAlerts(AlertRequest $request): JsonResponse
    {
        try {
            $alert = $this->alertService->createPaymentAlerts($request->validated());
            return $this->successResponse(
                new AlertResource($alert),
                'تم إنشاء التنبيه بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء التنبيه.',
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
                'تم تصفير العداد المطلوب.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء تحديث التنبيهات.',
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
                'تم جلب عدد التنبيهات غير المقروءة.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء جلب العدادات.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
