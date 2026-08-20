<?php
namespace App\Http\Controllers\Setting;
use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\GradeLevelResource;
use App\Models\GradeLevel;
use App\Models\GradeConfiguration;
use App\Models\Classroom;
use App\Services\Setting\GradeAndClassroomService;
use App\Http\Requests\Setting\StoreClassroomRequest;
use App\Http\Requests\Setting\StoreGradeConfigurationRequest;
use App\Http\Requests\Setting\StoreGradeLevelRequest;
use App\Http\Requests\Setting\UpdateClassroomRequest;
use App\Http\Requests\Setting\UpdateGradeConfigurationRequest;
use App\Http\Requests\Setting\UpdateGradeLevelRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Setting\GradeConfigurationResource;
use App\Http\Resources\Setting\ClassRoomResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;


class GradeAndClassroomController extends Controller
{
    use ApiResource;
    public function __construct(protected GradeAndClassroomService $service) {}


        public function indexGrades(): JsonResponse
    {
        try {
            $grades = $this->service->getAllGrades();
            $message = $grades->isEmpty() ? 'There are no grades registered yet.' : 'All grades retrieved successfully.';

            return $this->successResponse(
                GradeLevelResource::collection($grades),
                $message
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
        public function indexClassrooms(): JsonResponse
    {
        try {
            $classrooms = $this->service->getAllClassrooms();
            $message = $classrooms->isEmpty() ? 'There are no classrooms registered yet.' : 'All classrooms retrieved successfully.';

            return $this->successResponse(
                ClassroomResource::collection($classrooms),
                $message
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
        public function indexConfigurations(): JsonResponse
    {
        try {
            $configs = $this->service->getAllConfigurations();
            $message = $configs->isEmpty() ? 'There are no grade configurations registered yet.' : 'All grade configurations retrieved successfully.';

            return $this->successResponse(
                GradeConfigurationResource::collection($configs),
                $message
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

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
        public function showGrade(int $id): JsonResponse
    {
        try{
        return $this->successResponse(
            new GradeLevelResource($this->service->getGradeById($id)),
            'Grade details retrieved successfully.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('Grade not found', 404);
        }
    }

    public function showConfiguration(int $id): JsonResponse
    {
        try{
        return $this->successResponse(
            new GradeConfigurationResource($this->service->getConfigurationById($id)),
            'Grade configuration retrieved successfully.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('Grade configuration not found', 404);
        }
    }

    public function showClassroom(int $id): JsonResponse
    {
        try{
        return $this->successResponse(
            new ClassroomResource($this->service->getClassroomById($id)),
            'Classroom details retrieved successfully.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('Classroom not found', 404);
        }
    }
      public function destroyGrade(int $id): JsonResponse
    {
        try {
            $this->service->deleteGrade($id);
            return $this->successResponse(null, 'Grade deleted successfully.');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Grade not found', 404);

        } catch (Exception $e) {
            // هنا سيتم اصطياد الـ Exception الذي يحمل كود 409 من السيرفيس
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse('Error:Server', $statusCode, ['error' => $e->getMessage()]);
        }
    }

    public function destroyConfiguration(int $id): JsonResponse
    {
        try {
            $this->service->deleteConfiguration($id);
            return $this->successResponse(null, 'Grade configuration deleted successfully.');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Grade configuration not found', 404);

        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse('Error:Server', $statusCode, ['error' => $e->getMessage()]);
        }
    }

    public function destroyClassroom(int $id): JsonResponse
    {
        try {
            $this->service->deleteClassroom($id);
            return $this->successResponse(null, 'Classroom deleted successfully.');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Classroom not found', 404);

        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse('Error:Server', $statusCode, ['error' => $e->getMessage()]);
        }
    }

}
