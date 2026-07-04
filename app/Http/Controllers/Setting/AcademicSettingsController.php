<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\StructureResource;
use App\Models\AcademicSetting;
use App\Http\Requests\Setting\UpdateAcademicSettingsRequest;
use App\Services\Setting\AcademicSettingsService;
use App\Http\Resources\Setting\AcademicSettingsResource;
use App\ApiResource;
use App\Http\Requests\Setting\SemesterRequest;
use App\Http\Requests\Setting\StoreGradeLevelRequest;
use App\Http\Requests\Setting\UpdateClassroomRequest;
use App\Http\Requests\Setting\UpdateGradeLevelRequest;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Http\Requests\Setting\AcademicStageRequest;
use App\Models\AcademicYear;
use App\Http\Requests\Setting\AcademicYearRequest;
use App\Models\AcademicStage;
use App\Models\Semester;
use App\Http\Resources\Setting\AcademicYearResource;
use App\Http\Resources\Setting\SemesterResource;
use App\Http\Resources\Setting\AcademicStageResource;

class AcademicSettingsController extends Controller
{
    use ApiResource;

    private AcademicSettingsService $academicSettingsService;

    public function __construct(AcademicSettingsService $academicSettingsService)
    {
        $this->academicSettingsService = $academicSettingsService;
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
            return $this->errorResponse('Failed to create academic year.', 422, ['errors' => $e->errors()]);
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

    // ---- المراحل الدراسية ----
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

    
    // public function show()
    // {
    //     $settings = AcademicSetting::with(['gradeScales'])->where('school_id', 1)->firstOrFail();

    //     return $this->successResponse(
    //         new AcademicSettingsResource($settings),
    //         'Academic settings retrieved successfully.'
    //     );
    // }
    // // public function update(UpdateAcademicSettingsRequest $request, AcademicSettingsService $service)
    // // {
    // //     //   dd($request->all());
    // //     try {
    // //         $updatedSettings = $service->syncSettings($request->validated());

    // //         return $this->successResponse(
    // //             new AcademicSettingsResource($updatedSettings),
    // //             'Academic settings updated successfully.'
    // //         );
    // //     } catch (Exception $e) {
    // //         return $this->errorResponse('Failed to update settings.', 500, ['exception_message' => $e->getMessage()]);
    // //     }
    // // }


    // public function createStracture(StoreGradeLevelRequest $storeGradeLevelRequest)
    // {

    //     $gradeLevel = $this->academicSettingsService->createStructure($storeGradeLevelRequest->validated());

    //     return $this->successResponse(
    //         StructureResource::collection($gradeLevel),
    //         'school structure created successfuly',
    //         200
    //     );
    // }
    // public function showAllGrades()
    // {
    //     $levels = $this->academicSettingsService->listStructure();

    //     return $this->successResponse(
    //         StructureResource::collection($levels),
    //         'قائمة المراحل الدراسية',
    //         200
    //     );
    // }
    // public function showOneGrade(int $id)
    // {
    //     $level = $this->academicSettingsService->showLevel($id);

    //     if (!$level) {
    //         return $this->errorResponse('grade level dose not found', 404, null);
    //     }

    //     return $this->successResponse(
    //         new StructureResource($level),
    //         'تفاصيل المرحلة الدراسية',
    //         200
    //     );
    // }
    // public function updateGrade(UpdateGradeLevelRequest $request, int $id)
    // {
    //     $level = $this->academicSettingsService->updateLevel($id, $request->validated());

    //     return $this->successResponse(
    //         new StructureResource($level),
    //         'تم تحديث المرحلة الدراسية بنجاح.',
    //         200
    //     );
    // }
    // public function destroyGrade(int $id)
    // {
    //     $this->academicSettingsService->deleteLevel($id);

    //     return $this->successResponse(null, 'تم حذف المرحلة الدراسية بنجاح.', 200);
    // }

    
    // public function updateClassroom(UpdateClassroomRequest $request, int $id)
    // {
    //     $classRoom = $this->academicSettingsService->updateClassroom($id, $request->validated());

    //     return $this->successResponse(
    //         ['id' => $classRoom->id, 'name' => $classRoom->name, 'capacity' => $classRoom->capacity],
    //         'تم تحديث الشعبة بنجاح.',
    //         200
    //     );
    // }
    // public function destroyClassroom(int $id)
    // {
    //     $this->academicSettingsService->deleteClassroom($id);

    //     return $this->successResponse(null, 'تم حذف الشعبة بنجاح.', 200);
    // }
}
