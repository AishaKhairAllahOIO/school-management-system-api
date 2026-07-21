<?php

namespace App\Http\Controllers\Setting;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\StoreGradeSubjectRequest;
use App\Http\Requests\Setting\UpdateGradeSubjectRequest;
use App\Http\Resources\Setting\GradeSubjectResource;
use App\Models\GradeSubject;
use App\Services\Setting\GradeSubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class GradeSubjectController extends Controller
{
    use ApiResource;

    public function __construct(protected GradeSubjectService $gradeSubjectService) {}


    public function index(): JsonResponse
    {
        try {
            $gradeSubjects = $this->gradeSubjectService->getAllGradeSubjects();

            $message = $gradeSubjects->isEmpty() ? 'لا يوجد إعدادات مواد مسجلة بعد.' : 'تم جلب إعدادات المواد بنجاح.';

            return $this->successResponse(
                GradeSubjectResource::collection($gradeSubjects),
                $message,
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب إعدادات المواد.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $gradeSubject = GradeSubject::with(['academicYear', 'semester', 'gradeLevel', 'subject'])->findOrFail($id);

            return $this->successResponse(
                new GradeSubjectResource($gradeSubject),
                'تم جلب بيانات إعداد المادة بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'إعداد المادة المطلوب غير موجود.',
                404
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء جلب إعداد المادة.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }


    public function store(StoreGradeSubjectRequest $request): JsonResponse
    {
        try {
            $gradeSubject = $this->gradeSubjectService->createGradeSubject($request->validated());

            $gradeSubject->load(['academicYear', 'semester', 'gradeLevel', 'subject']);

            return $this->successResponse(
                new GradeSubjectResource($gradeSubject),
                'تم إضافة إعداد المادة بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إضافة إعداد المادة.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateGradeSubjectRequest $request, int $id): JsonResponse
    {
        try {
            $gradeSubject = GradeSubject::findOrFail($id);
            $updatedGradeSubject = $this->gradeSubjectService->updateGradeSubject($gradeSubject, $request->validated());

            $updatedGradeSubject->load(['academicYear', 'semester', 'gradeLevel', 'subject']);

            return $this->successResponse(
                new GradeSubjectResource($updatedGradeSubject),
                'تم تعديل إعداد المادة بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('إعداد المادة المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل إعداد المادة.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $gradeSubject = GradeSubject::findOrFail($id);
            $this->gradeSubjectService->deleteGradeSubject($gradeSubject);

            return $this->successResponse(null, 'تم حذف إعداد المادة بنجاح.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('إعداد المادة المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف إعداد المادة.', 500, ['error' => $e->getMessage()]);
        }
    }
}
