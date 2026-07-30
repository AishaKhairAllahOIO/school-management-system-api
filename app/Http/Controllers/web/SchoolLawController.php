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
use Exception;
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
                'تم جلب القوانين المدرسية بنجاح.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء جلب القوانين المدرسية.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function store(StoreSchoolLawRequest $request): JsonResponse
    {
        try {
            $law = $this->schoolLawService->createLaw($request->validated());

            return $this->successResponse(
                new SchoolLawResource($law),
                'تمت إضافة القانون بنجاح.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء إضافة القانون.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $law = SchoolLaw::findOrFail($id);
            return $this->successResponse(
                new SchoolLawResource($law),
                'تم جلب القانون بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('القانون المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء عرض القانون.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function update(UpdateSchoolLawRequest $request, $id): JsonResponse
    {
        try {

            $law = SchoolLaw::findOrFail($id);
            $updatedLaw = $this->schoolLawService->updateLaw($law, $request->validated());

            return $this->successResponse(
                new SchoolLawResource($updatedLaw),
                'تم تحديث القانون بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('القانون المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء تحديث القانون.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $law = SchoolLaw::findOrFail($id);
            $this->schoolLawService->deleteLaw($law);

            return $this->successResponse(
                null,
                'تم حذف القانون بنجاح.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('القانون المطلوب غير موجود.', 404);
        } catch (Exception $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء حذف القانون.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
