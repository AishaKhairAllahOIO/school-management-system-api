<?php

namespace App\Http\Controllers\Web;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreSchoolLawRequest;
use App\Http\Requests\Web\UpdateSchoolLawRequest;
use App\Http\Resources\Web\SchoolLawResource;
use App\Models\SchoolLaw;
use App\Services\Web\SchoolLawService;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SchoolLawController extends Controller
{
    use ApiResource;

    private SchoolLawService $schoolLawService;

    public function __construct(SchoolLawService $schoolLawService)
    {
        $this->schoolLawService = $schoolLawService;
    }

    public function index(): JsonResponse
    {
        try {
            $laws = $this->schoolLawService->getAllLaws();

            return $this->successResponse(
                SchoolLawResource::collection($laws),
                'School laws retrieved successfully.',
                200
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function store(StoreSchoolLawRequest $request): JsonResponse
    {
        try {
            $law = $this->schoolLawService->createLaw($request->validated());

            return $this->successResponse(
                new SchoolLawResource($law),
                'School law created successfully.',
                201
            );
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $law = SchoolLaw::findOrFail($id);
            return $this->successResponse(
                new SchoolLawResource($law),
                'School law details retrieved successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('School law not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function update(UpdateSchoolLawRequest $request, $id): JsonResponse
    {
        try {
            $law = SchoolLaw::findOrFail($id);
            $updatedLaw = $this->schoolLawService->updateLaw($law, $request->validated());

            return $this->successResponse(
                new SchoolLawResource($updatedLaw),
                'School law updated successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('School law not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $law = SchoolLaw::findOrFail($id);
            $this->schoolLawService->deleteLaw($law);

            return $this->successResponse(
                null,
                'School law deleted successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('School law not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }
}