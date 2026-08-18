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

            $message = $subjects->isEmpty() ? 'There are no subjects registered yet.' : 'All subjects retrieved successfully.';

            return $this->successResponse($subjects, $message, 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function store(StoreSubjectRequest $request): JsonResponse
    {
        try {
            $subject = $this->subjectService->createSubject($request->validated());

            return $this->successResponse($subject, 'Subject created successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update(UpdateSubjectRequest $request, int $id): JsonResponse
    {
        try {
            $subject = Subject::findOrFail($id);

            $updatedSubject = $this->subjectService->updateSubject($subject, $request->validated());

            return $this->successResponse($updatedSubject, 'Subject updated successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested subject does not exist.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $subject = Subject::findOrFail($id);

            $subject->delete();

            return $this->successResponse(null, 'Subject deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested subject does not exist.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}
