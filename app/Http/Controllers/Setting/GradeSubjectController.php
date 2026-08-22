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
use Illuminate\Validation\ValidationException;

class GradeSubjectController extends Controller
{
    use ApiResource;

    public function __construct(protected GradeSubjectService $gradeSubjectService)
    {
    }


    public function index(): JsonResponse
    {
        try {
            $gradeSubjects = $this->gradeSubjectService->getAllGradeSubjects();

            $message = $gradeSubjects->isEmpty() ? 'There are no grade subjects registered yet.' : 'All grade subjects retrieved successfully.';

            return $this->successResponse(
                GradeSubjectResource::collection($gradeSubjects),
                $message,
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $gradeSubject = GradeSubject::with(['academicYear', 'semester', 'gradeLevel', 'subject'])->findOrFail($id);

            return $this->successResponse(
                new GradeSubjectResource($gradeSubject),
                'Grade subject information retrieved successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'The requested grade subject does not exist.',
                404
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
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
                'Grade subject created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
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
                'Grade subject updated successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested grade subject does not exist.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $gradeSubject = GradeSubject::findOrFail($id);
            $this->gradeSubjectService->deleteGradeSubject($gradeSubject);

            return $this->successResponse(null, 'Grade subject deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested grade subject does not exist.', 404);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
