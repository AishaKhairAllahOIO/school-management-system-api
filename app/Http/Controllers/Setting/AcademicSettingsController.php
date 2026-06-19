<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\StructureResource;
use App\Models\AcademicSetting;
use App\Http\Requests\Setting\UpdateAcademicSettingsRequest;
use App\Services\Setting\AcademicSettingsService;
use App\Http\Resources\Setting\AcademicSettingsResource;
use App\ApiResource;
use App\Http\Requests\Setting\StoreGradeLevelRequest;
use App\Http\Requests\Setting\UpdateClassroomRequest;
use App\Http\Requests\Setting\UpdateGradeLevelRequest;
use Illuminate\Validation\ValidationException;
use Exception;

class AcademicSettingsController extends Controller
{
    use ApiResource;

    private AcademicSettingsService $academicSettingsService;

    public function __construct(AcademicSettingsService $academicSettingsService)
    {
        $this->academicSettingsService = $academicSettingsService;
    }
    public function show()
    {
        $settings = AcademicSetting::with(['gradeScales'])->where('school_id', 1)->firstOrFail();

        return $this->successResponse(
            new AcademicSettingsResource($settings),
            'Academic settings retrieved successfully.'
        );
    }
    public function update(UpdateAcademicSettingsRequest $request, AcademicSettingsService $service)
    {
        //   dd($request->all());
        try {
            $updatedSettings = $service->syncSettings($request->validated());

            return $this->successResponse(
                new AcademicSettingsResource($updatedSettings),
                'Academic settings updated successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update settings.', 500, ['exception_message' => $e->getMessage()]);
        }
    }


    public function createStracture(StoreGradeLevelRequest $storeGradeLevelRequest)
    {

        $gradeLevel = $this->academicSettingsService->createStructure($storeGradeLevelRequest->validated());

        return $this->successResponse(
            StructureResource::collection($gradeLevel),
            'school structure created successfuly',
            200
        );
    }
    public function showAllGrades()
    {
        $levels = $this->academicSettingsService->listStructure();

        return $this->successResponse(
            StructureResource::collection($levels),
            'قائمة المراحل الدراسية',
            200
        );
    }
    public function showOneGrade(int $id)
    {
        $level = $this->academicSettingsService->showLevel($id);

        if (!$level) {
            return $this->errorResponse('grade level dose not found', 404, null);
        }

        return $this->successResponse(
            new StructureResource($level),
            'تفاصيل المرحلة الدراسية',
            200
        );
    }
    public function updateGrade(UpdateGradeLevelRequest $request, int $id)
    {
        $level = $this->academicSettingsService->updateLevel($id, $request->validated());

        return $this->successResponse(
            new StructureResource($level),
            'تم تحديث المرحلة الدراسية بنجاح.',
            200
        );
    }
    public function destroyGrade(int $id)
    {
        $this->academicSettingsService->deleteLevel($id);

        return $this->successResponse(null, 'تم حذف المرحلة الدراسية بنجاح.', 200);
    }

    
    public function updateClassroom(UpdateClassroomRequest $request, int $id)
    {
        $classRoom = $this->academicSettingsService->updateClassroom($id, $request->validated());

        return $this->successResponse(
            ['id' => $classRoom->id, 'name' => $classRoom->name, 'capacity' => $classRoom->capacity],
            'تم تحديث الشعبة بنجاح.',
            200
        );
    }
    public function destroyClassroom(int $id)
    {
        $this->academicSettingsService->deleteClassroom($id);

        return $this->successResponse(null, 'تم حذف الشعبة بنجاح.', 200);
    }
}
