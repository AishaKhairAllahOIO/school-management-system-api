<?php
namespace App\Http\Controllers\Setting;
use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\GradeLevelResource;
use App\Models\GradeLevel;
use App\Models\GradeConfiguration;
use App\Models\Classroom;
use App\Services\Setting\GradeAndClassroomService;
use App\Http\Requests\Setting\GradeRequest;
use App\Http\Requests\Setting\GradeConfigurationRequest;
use App\Http\Requests\Setting\ClassroomRequest;
use App\Http\Requests\Setting\StoreClassroomRequest;
use App\Http\Requests\Setting\StoreGradeConfigurationRequest;
use App\Http\Requests\Setting\StoreGradeLevelRequest;
use App\Http\Requests\Setting\UpdateClassroomRequest;
use App\Http\Requests\Setting\UpdateGradeConfigurationRequest;
use App\Http\Requests\Setting\UpdateGradeLevelRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Setting\GradeConfigurationResource;
use App\Http\Resources\Setting\ClassRoomResource;

class GradeAndClassroomController extends Controller
{
    use ApiResource;
    public function __construct(protected GradeAndClassroomService $service) {}

    // ---- الصفوف ----
    public function storeGrade(StoreGradeLevelRequest $request): JsonResponse {
        return $this->successResponse(new GradeLevelResource(
            $this->service->createGrade($request->validated())),
            'Grade created successfully.', 201
        );
    }
    public function updateGrade(UpdateGradeLevelRequest $request, GradeLevel $grade): JsonResponse {
        return $this->successResponse(new GradeLevelResource(
            $this->service->updateGrade($grade,$request->validated() )),
            'Grade updated successfully.'
        );
    }

    // ---- التكوين التخطيطي للصف ----
    public function storeConfiguration(StoreGradeConfigurationRequest $request): JsonResponse {
        return $this->successResponse(new GradeConfigurationResource(
            $this->service->createConfiguration($request->validated())),
            'Grade configuration created successfully.', 201
        );
    }
    public function updateConfiguration(UpdateGradeConfigurationRequest $request, GradeConfiguration $config): JsonResponse {
        return $this->successResponse(new GradeConfigurationResource(
            $this->service->updateConfiguration($config,$request->validated())),
            'Grade configuration updated successfully.'
        );
    }

    // ---- الشعب الدراسية ----
    public function storeClassroom(StoreClassroomRequest $request): JsonResponse {
        return $this->successResponse(new ClassRoomResource(
            $this->service->createClassroom($request->validated())),
            'Classroom created successfully.', 201
        );
    }
    public function updateClassroom(UpdateClassroomRequest $request, Classroom $classroom): JsonResponse {
        return $this->successResponse(new ClassRoomResource(
            $this->service->updateClassroom($classroom,$request->validated() )),
            'Classroom updated successfully.'
        );
    }
}