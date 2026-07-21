<?php

namespace App\Http\Controllers\Setting;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\GroupedAssessmentResource;
use App\Models\AssessmentComponent;
use App\Http\Requests\Setting\StoreAssessmentComponentRequest;
use App\Http\Requests\Setting\UpdateAssessmentComponentRequest;
use App\Http\Resources\Setting\AssessmentComponentResource;
use App\Services\Setting\AssessmentComponentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class AssessmentComponentController extends Controller
{
    use ApiResource;

    public function __construct(protected AssessmentComponentService $assessmentComponentService) {}


    public function index(Request $request): JsonResponse
    {
        try {
            $components = $this->assessmentComponentService->getComponents($request->input('grade_subject_id'));

            $message = $components->isEmpty() ? 'لا يوجد مكونات تقييم مسجلة بعد.' : 'تم جلب مكونات التقييم بنجاح.';

            return $this->successResponse(
                AssessmentComponentResource::collection($components),
                $message,
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب مكونات التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function store(StoreAssessmentComponentRequest $request): JsonResponse
    {
        try {
            $component = $this->assessmentComponentService->createComponent($request->validated());

            return $this->successResponse(
                new AssessmentComponentResource($component),
                'تم إضافة مكون التقييم بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إضافة مكون التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $component = AssessmentComponent::findOrFail($id);

            return $this->successResponse(
                new AssessmentComponentResource($component),
                'تم جلب تفاصيل التقييم بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('مكون التقييم المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب تفاصيل التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateAssessmentComponentRequest $request, int $id): JsonResponse
    {
        try {
            $component = AssessmentComponent::findOrFail($id);
            $updatedComponent = $this->assessmentComponentService->updateComponent($component, $request->validated());

            return $this->successResponse(
                new AssessmentComponentResource($updatedComponent),
                'تم تعديل مكون التقييم بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('مكون التقييم المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تعديل مكون التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $component = AssessmentComponent::findOrFail($id);
            $this->assessmentComponentService->deleteComponent($component);

            return $this->successResponse(null, 'تم حذف مكون التقييم بنجاح.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('مكون التقييم المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف مكون التقييم.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function groupedBySubject(): JsonResponse
    {
        try {
            $data = $this->assessmentComponentService->getGroupedSubjectsWithComponents();

            return $this->successResponse(
                GroupedAssessmentResource::collection($data),
                'تم جلب إعدادات المواد مع مكونات التقييم بنجاح.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب البيانات.', 500, ['error' => $e->getMessage()]);
        }
    }
}
