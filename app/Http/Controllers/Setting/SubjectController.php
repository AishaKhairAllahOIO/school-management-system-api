<?php

namespace App\Http\Controllers\Setting;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\StoreSubjectRequest;
use App\Http\Requests\Setting\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\Setting\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SubjectController extends Controller
{
    use ApiResource;

    public function __construct(protected SubjectService $subjectService) {}


    public function index(): JsonResponse
    {
        try {
            $subjects = Subject::orderBy('subject_name', 'asc')->get();

            $message = $subjects->isEmpty() ? 'لا يوجد مواد دراسية مسجلة بعد.' : 'تم جلب المواد بنجاح.';

            return $this->successResponse($subjects, $message, 200);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب المواد.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function store(StoreSubjectRequest $request): JsonResponse
    {
        try {
            $subject = $this->subjectService->createSubject($request->validated());

            return $this->successResponse($subject, 'تم إضافة المادة الجديدة بنجاح.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إضافة المادة.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateSubjectRequest $request, int $id): JsonResponse
    {
        try {
            $subject = Subject::findOrFail($id);

            $updatedSubject = $this->subjectService->updateSubject($subject, $request->validated());

            return $this->successResponse($updatedSubject, 'تم تعديل المادة بنجاح.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('المادة المطلوبة غير موجودة.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل المادة.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $subject = Subject::findOrFail($id);

            $subject->delete();

            return $this->successResponse(null, 'تم حذف المادة بنجاح.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('المادة المطلوبة غير موجودة.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف المادة.', 500, ['error' => $e->getMessage()]);
        }
    }
}
