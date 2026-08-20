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

            $message = $components->isEmpty() ? 'No assessment components registered yet.' : 'Assessment components fetched successfully.';

            return $this->successResponse(
                AssessmentComponentResource::collection($components),
                $message,
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function store(StoreAssessmentComponentRequest $request): JsonResponse
    {
        try {
            $component = $this->assessmentComponentService->createComponent($request->validated());

            return $this->successResponse(
                new AssessmentComponentResource($component),
                'Assessment Component created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $component = AssessmentComponent::findOrFail($id);

            return $this->successResponse(
                new AssessmentComponentResource($component),
                'Assessment Component details retrieved successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Assessment Component not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateAssessmentComponentRequest $request, int $id): JsonResponse
    {
        try {
            $component = AssessmentComponent::findOrFail($id);
            $updatedComponent = $this->assessmentComponentService->updateComponent($component, $request->validated());

            return $this->successResponse(
                new AssessmentComponentResource($updatedComponent),
                'Assessment Component updated successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Assessment Component not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $component = AssessmentComponent::findOrFail($id);
            $this->assessmentComponentService->deleteComponent($component);

            return $this->successResponse(null, 'Assessment Component deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Assessment Component not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function groupedBySubject(): JsonResponse
    {
        try {
            $data = $this->assessmentComponentService->getGroupedSubjectsWithComponents();

            return $this->successResponse(
                GroupedAssessmentResource::collection($data),
                'Assessment components grouped by subjects retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
