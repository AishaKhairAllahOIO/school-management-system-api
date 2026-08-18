<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateAcademicSettingsRequest;
use App\Services\Setting\AcademicSettingsService;
use App\Http\Resources\Setting\AcademicSettingsResource;
use App\ApiResource;
use App\Http\Requests\Setting\SemesterRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Setting\AcademicStageRequest;
use App\Models\AcademicYear;
use App\Http\Requests\Setting\AcademicYearRequest;
use App\Models\AcademicStage;
use App\Models\Semester;
use Exception;
use App\Http\Resources\Setting\AcademicYearResource;
use App\Http\Resources\Setting\SemesterResource;
use App\Http\Resources\Setting\AcademicStageResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\AcademicStageType;
use App\Enums\GradeName;
use Throwable;

class AcademicSettingsController extends Controller
{
    use ApiResource;

    private AcademicSettingsService $academicSettingsService;

    public function __construct(AcademicSettingsService $academicSettingsService)
    {
        $this->academicSettingsService = $academicSettingsService;
    }
     public function getYears(AcademicSettingsService $service) {
        return $this->successResponse(
            AcademicYearResource::collection($service->getAllYears()),
            'Academic years retrieved successfully.'
        );
    }

    public function getTerms(AcademicSettingsService $service) {
        return $this->successResponse(
            SemesterResource::collection($service->getAllTerms()),
            'Academic terms retrieved successfully.'
        );
    }

    public function getStages(AcademicSettingsService $service) {
        return $this->successResponse(
            AcademicStageResource::collection($service->getAllStages()),
            'Academic stages retrieved successfully.'
        );
    }
      public function showYear(int $id, AcademicSettingsService $service) {
        try{
        return $this->successResponse(
            new AcademicYearResource($service->getYearById($id)),
            'Academic year retrieved successfully.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('Academic year not found', 404);
        }
      
    }

    public function showTerm(int $id, AcademicSettingsService $service) {
        try{
        return $this->successResponse(
            new SemesterResource($service->getTermById($id)),
            'Academic term retrieved successfully.'
        );
        }
        catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('Academic term not found', 404);
        }
    }

    public function showStage(int $id, AcademicSettingsService $service) {
        try{
        return $this->successResponse(
            new AcademicStageResource($service->getStageById($id)),
            'Academic stage retrieved successfully.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('Academic stage not found', 404);
        }
    }

      public function index(AcademicSettingsService $service) {
        return $this->successResponse(
            new AcademicSettingsResource($service->getAcademicViewData()),
            'Academic settings retrieved successfully.'
        );
    }

    public function update(UpdateAcademicSettingsRequest $request,AcademicSettingsService $service) {
        return $this->successResponse(
            new AcademicSettingsResource($service->updateSettings($request->validated())),
            'Academic settings updated successfully.',201
        );
    }

    // ---- الأعوام الدراسية ----
    public function storeYear(AcademicYearRequest $request, AcademicSettingsService $service) {
        try{
        return $this->successResponse(
            new AcademicYearResource($service->saveYear($request->validated())),
            'Academic year created successfully.'
        );
        }catch (ValidationException $e) {
            return $this->errorResponse('Error:Server', 500, ['errors' => $e->errors()]);
        }
    }

    public function updateYear(AcademicYearRequest $request, AcademicYear $year, AcademicSettingsService $service) {
        try{
        return $this->successResponse(
            new AcademicYearResource($service->saveYear($request->validated(), $year)),
            'Academic year updated successfully.'
        );
        }catch (ValidationException $e) {
            return $this->errorResponse('Failed to update academic year.', 422, ['errors' => $e->errors()]);
        }
    }


    // ---- الفصول الدراسية ----
    public function storeTerm(SemesterRequest $request, AcademicSettingsService $service) {
        try{
        return $this->successResponse(
            new SemesterResource($service->saveTerm($request->validated())),
            'Semester created successfully.'
        );
        }
        catch(ValidationException $e)
        {
            return $this->errorResponse('Failed to update academic term.', 422, ['errors' => $e->errors()]);

        }
    }

    public function updateTerm(SemesterRequest $request, Semester $term, AcademicSettingsService $service) {
        return $this->successResponse(
            new SemesterResource($service->saveTerm($request->validated(), $term)),
            'Semester updated successfully.'
        );
    }

    public function storeStage(AcademicStageRequest $request, AcademicSettingsService $service) {
        return $this->successResponse(
            new AcademicStageResource($service->saveStage($request->validated())),
            'Academic stage created successfully.'
        );
    }

    public function updateStage(AcademicStageRequest $request, AcademicStage $stage, AcademicSettingsService $service) {
        return $this->successResponse(
            new AcademicStageResource($service->saveStage($request->validated(), $stage)),
            'Academic stage updated successfully.'
        );
    }
        public function destroy(AcademicSettingsService $service) {
        try {
            $service->deleteSettings();
            return $this->successResponse(null, 'Academic settings deleted successfully.');
        } catch (Exception $e) {
           return $this->errorResponse('Can not delete Academic setting which is under using', 409, ['error' => $e->getMessage()]);
        }
    }
        public function destroyYear(int $id, AcademicSettingsService $service) {
        try {
            $service->deleteYear($id);
            return $this->successResponse(null, 'Academic year deleted successfully.');
        }catch(ModelNotFoundException $e) {
            return $this->errorResponse('Academic year not found', 404);
        }catch (\Exception $e) {
            return $this->errorResponse('Can not delete Academic year which is under using', 409, ['error' => $e->getMessage()]);
        }
    }
        public function destroyStage(int $id, AcademicSettingsService $service) {
        try {
            $service->deleteStage($id);
            return $this->successResponse(null, 'Academic stage deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Academic stage not found', 404);
        }  catch (\Exception $e) {
            return $this->errorResponse('Can not delete Stag which is under using ', 409, ['error' => $e->getMessage()]);
        }
    }
        public function destroyTerm(int $id, AcademicSettingsService $service) {
        try {
            $service->deleteTerm($id);
            return $this->successResponse(null, 'Academic term deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Academic term not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Can not delete Term which is under using', 409, ['error' => $e->getMessage()]);
        }
    }

    public function statistics(AcademicSettingsService $academicSettingsService)
    {
        $statistics = $academicSettingsService->getAcademicStatistics();

        return $this->successResponse($statistics,'Statistics shown successfully.',200);
    }
    public function indexWithGrades()
    {
        try {
            $groupedData = collect(AcademicStageType::cases())->map(function (AcademicStageType $stage) {
                $grades = collect(GradeName::getGradesByStage($stage))->map(function (GradeName $grade) {
                    return [
                        'key'      => $grade->value,
                        'label_ar' => $grade->labelAr(),
                    ];
                });

                return [
                    'stage'          => $stage->value,
                    'stage_label_ar' => $stage->labelAr(),
                    'grades'         => $grades,
                ];
            });

            return $this->successResponse(
                $groupedData,
                'Academic stages and grades fetched successfully.'
            );

        } catch (Throwable $e) {
            return $this->errorResponse(
               'Error:Server' . $e->getMessage(),
                500
            );
        }
    }


}
