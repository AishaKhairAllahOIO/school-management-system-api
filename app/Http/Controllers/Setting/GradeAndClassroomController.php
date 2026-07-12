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
use Exception; // 👈 هذا الكلاس الذي يصطاد عدم وجود الداتا


class GradeAndClassroomController extends Controller
{
    use ApiResource;
    public function __construct(protected GradeAndClassroomService $service) {}

    // ---- الصفوف ----
        public function indexGrades(): JsonResponse 
    {
        try {
            $grades = $this->service->getAllGrades();
            // لمسة إضافية: تغيير الرسالة إذا كانت القائمة فارغة
            $message = $grades->isEmpty() ? 'لا يوجد صفوف دراسية مسجلة بعد.' : 'تم جلب جميع الصفوف الدراسية بنجاح.';
            
            return $this->successResponse(
                GradeLevelResource::collection($grades),
                $message
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب الصفوف.', 500, ['error' => $e->getMessage()]);
        }
    }
        public function indexClassrooms(): JsonResponse 
    {
        try {
            $classrooms = $this->service->getAllClassrooms();
            $message = $classrooms->isEmpty() ? 'لا يوجد شعب دراسية مسجلة بعد.' : 'تم جلب جميع الشعب الدراسية بنجاح.';

            return $this->successResponse(
                ClassroomResource::collection($classrooms),
                $message
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب الشعب.', 500, ['error' => $e->getMessage()]);
        }
    }
        public function indexConfigurations(): JsonResponse 
    {
        try {
            $configs = $this->service->getAllConfigurations();
            $message = $configs->isEmpty() ? 'لا يوجد إعدادات تخطيطية مسجلة بعد.' : 'تم جلب جميع الإعدادات التخطيطية بنجاح.';

            return $this->successResponse(
                GradeConfigurationResource::collection($configs),
                $message
            );
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب الإعدادات.', 500, ['error' => $e->getMessage()]);
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
        public function showGrade(int $id): JsonResponse 
    {
        try{
        return $this->successResponse(
            new GradeLevelResource($this->service->getGradeById($id)),
            'تم جلب بيانات الصف الدراسي بنجاح.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('الصف الدراسي المطلوب غير موجود.', 404);
        }
    }

    public function showConfiguration(int $id): JsonResponse 
    {
        try{
        return $this->successResponse(
            new GradeConfigurationResource($this->service->getConfigurationById($id)),
            'تم جلب الإعداد التخطيطي بنجاح.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('الإعداد التخطيطي المطلوب غير موجود.', 404);
        }
    }

    public function showClassroom(int $id): JsonResponse 
    {
        try{
        return $this->successResponse(
            new ClassroomResource($this->service->getClassroomById($id)),
            'تم جلب بيانات الشعبة بنجاح.'
        );
        }catch(ModelNotFoundException $e)
        {
         return $this->errorResponse('الشعبة الدراسية المطلوبة غير موجودة.', 404);
        }
    }
      public function destroyGrade(int $id): JsonResponse 
    {
        try {
            $this->service->deleteGrade($id);
            return $this->successResponse(null, 'تم حذف الصف الدراسي بنجاح.');
            
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الصف الدراسي المطلوب غير موجود.', 404);
            
        } catch (Exception $e) {
            // هنا سيتم اصطياد الـ Exception الذي يحمل كود 409 من السيرفيس
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    public function destroyConfiguration(int $id): JsonResponse 
    {
        try {
            $this->service->deleteConfiguration($id);
            return $this->successResponse(null, 'تم حذف الإعداد التخطيطي بنجاح.');
            
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الإعداد التخطيطي المطلوب غير موجود.', 404);
            
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    public function destroyClassroom(int $id): JsonResponse 
    {
        try {
            $this->service->deleteClassroom($id);
            return $this->successResponse(null, 'تم حذف الشعبة الدراسية بنجاح.');
            
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الشعبة الدراسية المطلوبة غير موجودة.', 404);
            
        } catch (Exception $e) {
            $statusCode = $e->getCode() == 409 ? 409 : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

}