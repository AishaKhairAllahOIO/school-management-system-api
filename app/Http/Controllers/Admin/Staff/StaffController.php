<?php
namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\ImportBatch;
use App\ApiResource;
use App\Http\Resources\Staff\StaffProfileResource;
use App\Http\Requests\Admin\Staff\StoreStaffRequest;
use App\Http\Requests\Admin\Staff\UpdatePersonalStaffRequest;
use App\Http\Requests\Admin\Staff\UpdateEmploymentStaffRequest;
use App\Http\Requests\Admin\Student\ImportExalSheetStudentRequest; // (يُفضل تغييره للاسم الموحد الذي اتفقنا عليه سابقاً)
use App\Services\Staff\StaffRegisterService;
use App\Services\Staff\StaffManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Exception;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nette\Schema\ValidationException;
use App\Services\Staff\TeacherWorkloadService;
use App\Http\Resources\Staff\TeacherWorkloadResource;
use App\Http\Requests\Admin\Staff\StoreTeacherWorkloadRequest;
use App\Http\Requests\Admin\Staff\UpdateTeacherWorkloadRequest;
use App\Models\TeacherAssignment;
use App\Http\Resources\Staff\TeacherAssignmentResource;
use App\Http\Requests\Admin\Staff\UpdateTeacherAssignmentRequest;
use App\Http\Requests\Admin\Staff\StoreTeacherAssignmentRequest;
use App\Http\Requests\Admin\Staff\UpdateStaffPersonalDataRequest;

class StaffController extends Controller
{
    use ApiResource;

    // حقن السيرفسين معاً بشكل نظيف
    public function __construct(
        private StaffRegisterService $registerService,
        private StaffManagementService $managementService,
        private TeacherWorkloadService $workloadService 

    ) {}
     public function setWorkload(StoreTeacherWorkloadRequest $request): JsonResponse
    {
        try {
            $workload = $this->workloadService->createWorkload($request->validated());
            return $this->successResponse(new TeacherWorkloadResource($workload), 'Teacher workload assigned successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getWorkloads(int $staff): JsonResponse
    {
        try {
            $workloads = $this->workloadService->getTeacherWorkloads($staff);
            return $this->successResponse(TeacherWorkloadResource::collection($workloads), 'Teacher workloads retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    // 🔥 دالة التعديل للنصاب
    public function updateWorkload(UpdateTeacherWorkloadRequest $request, int $staff,  int $workload): JsonResponse
    {
        try {
            $updatedWorkload = $this->workloadService->updateWorkload($workload, $request->validated());
            return $this->successResponse(new TeacherWorkloadResource($updatedWorkload), 'Teacher workload updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    // 🔥 دالة الحذف للنصاب
    public function destroyWorkload(int $staff,  int $workload): JsonResponse
    {
        try {
            $this->workloadService->deleteWorkload($workload);
            return $this->successResponse(null, 'Teacher workload deleted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
     public function assignClassrooms(StoreTeacherAssignmentRequest $request): JsonResponse
    {
        try {
            $assignments = $this->workloadService->assignTeacher($request->validated());
            return $this->successResponse(TeacherAssignmentResource::collection($assignments), 'Teacher assignment created successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 422, ['error' => $e->getMessage()]);
        }
    }

    public function getAssignments(int $staff, Request $request): JsonResponse
    {
        try {
            // $yearId = $request->query('academic_year_id');
            // if (!$yearId) {
            //     return $this->errorResponse('يرجى تحديد السنة الدراسية (academic_year_id).', 422);
            // }

            $assignments = $this->workloadService->getTeacherAssignments($staff);
            return $this->successResponse(TeacherAssignmentResource::collection($assignments), 'Teacher assignments retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    // 🔥 تعديل السينيور: استقبال ID كـ int
    public function updateAssignment(UpdateTeacherAssignmentRequest $request, int $staff, int $assignmentId): JsonResponse
    {
        try {
            $assignment = TeacherAssignment::findOrFail($assignmentId);
            $updatedAssignment = $this->workloadService->updateAssignment($assignment, $request->validated());
            return $this->successResponse(new TeacherAssignmentResource($updatedAssignment), 'Teacher assignment updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Teacher assignment record not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    // 🔥 التنظيف النهائي: إزالة الاعتماد على temporary_periods_count من الفرونت إند تماماً
    public function destroyAssignment(Request $request, int $staff, int $assignmentId): JsonResponse
    {
        try {
            $assignment = TeacherAssignment::findOrFail($assignmentId);
            
            // السيرفس الآن يعتمد على الداتابيز لحساب ما يجب استرجاعه من حصص
            $this->workloadService->deleteAssignment($assignment);
            
            return $this->successResponse(null, 'Teacher assignment deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Teacher assignment record not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

 public function store(StoreStaffRequest $request): JsonResponse
    {
        try {
            $staff = $this->registerService->registerSingleStaff($request->validated());
            return $this->successResponse(new StaffProfileResource($staff), 'Teacher registered successfully.', 201);
        }  catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function importExcel(ImportExalSheetStudentRequest $request,string $role): JsonResponse
    {
        try {
            $batch = $this->registerService->initiateStaffExcelImport($request->file('excel_file'), $role ,$request->user()->id);
            return $this->successResponse(['batch_id' => $batch->id], 'Teacher excel file uploaded successfully.', 202);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
        public function getImportStatus($batch)
    {
       $batch = ImportBatch::find($batch); 

     if(!$batch) {
            return $this->errorResponse('Teacher excel file not found.', 404);
        }        
        return $this->successResponse([
            'batch_id'        => $batch->id,
            'file_name'       => $batch->batch_title,
            'status'          => $batch->status, // (pending, processing, completed, failed)
            'total_rows'      => $batch->total_rows,
            'processed_rows'  => $batch->processed_rows,
            'successful_rows' => $batch->successful_rows,
            'failed_rows'     => $batch->failed_rows,
            'has_errors'      => ($batch->failed_rows > 0|| $batch->status === 'failed'),
        ], 'Teacher import status fetched successfully.');
    }
public function exportErrors(ImportBatch $batch, StaffRegisterService $service)
    {
        try{
        return $service->downloadBatchErrors($batch);
        }
        catch(Exception $e)
        {
            return $this->successResponse(null,'No error to show',200);
        }catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500);
        }
    }
    // ==========================================
    // 2. إدارة الموظفين (CRUD & Management)
    // ==========================================
 public function roleCounts(): JsonResponse
    {
        try {
            $counts = $this->managementService->getStaffRoleCounts();
            return $this->successResponse($counts, 'Staff role statistics retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getByRole(string $role, Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 15);
            $staff = $this->managementService->getStaffByRole($role, $perPage);
            // استخدمنا Resource Collection إذا كانت النتائج Paginated
            return $this->successResponse($staff, "Teacher records for role {$role} fetched successfully.");
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function index(): JsonResponse
    {
        try {
            $staff = $this->managementService->getAllStaff();
            return $this->successResponse(StaffProfileResource::collection($staff)->response()->getData(true), 'Teacher profiles fetched successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show(int $staff): JsonResponse
    {
        try {
            $staffData = $this->managementService->getStaffById($staff);
            return $this->successResponse(new StaffProfileResource($staffData), 'Teacher details fetched successfully.');
        }catch(ModelNotFoundException $e) 
        {
            return $this->errorResponse('Teacher not found',404);
        }
        catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
 

    public function updatePersonal(UpdateStaffPersonalDataRequest $request, int $staff): JsonResponse
    {
        try {
            $updated = $this->managementService->updatePersonalData($staff, $request->validated());
            return $this->successResponse(new StaffProfileResource($updated), 'Teacher personal data updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Teacher not found', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    // public function updateEmployment(UpdateEmploymentStaffRequest $request, int $staff): JsonResponse
    // {
    //     try {
    //         $updated = $this->managementService->updateEmploymentData($staff, $request->validated());
    //         return $this->successResponse(new StaffProfileResource($updated), 'تم تحديث البيانات الوظيفية للموظف بنجاح.');
    //     } 
    //     catch (ModelNotFoundException $e) {
    //         return $this->errorResponse('المستخدم غير مموجود ', 404);
    //     }
    //     catch (Exception $e) {
    //         return $this->errorResponse('حدث خطأ أثناء تحديث البيانات الوظيفية.', 500, ['error' => $e->getMessage()]);
    //     }
    // }

    public function search(string $role,Request $request): JsonResponse
    {
        try {
            $fullName = $request->query('name');
            if (empty($fullName)) {
                return $this->errorResponse('Please specify a name to search for.', 422);
            }
            $perPage = $request->query('per_page', 15);
            $results = $this->managementService->searchStaffByRoleAndName($role,$fullName, $perPage);
            return $this->successResponse(StaffProfileResource::collection($results), 'Search results fetched successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function alphabetical(Request $request): JsonResponse
    { 
       try {
            $direction = $request->query('direction', 'asc');
            $perPage = $request->query('per_page', 15);

            $staff = $this->managementService->getAllStaffAlphabetically($direction, $perPage);
            return $this->successResponse(StaffProfileResource::collection($staff)->response()->getData(true), 'Teachers fetched alphabetically successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    
    public function myProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $staffRecord = Staff::where('user_id', $user->id)->firstOrFail();

            $staffData = $this->managementService->getStaffProfile($staffRecord->id);
            
            return $this->successResponse(new StaffProfileResource($staffData), 'Personal profile fetched successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Your current account is not registered as a staff member in the system.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function toggleStatus(int $staff): JsonResponse
    {
        try {
            $statusText = $this->managementService->toggleAccountStatus($staff);
            return $this->successResponse(null, "Staff account status toggled successfully to: {$statusText}.");
        }catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff member not found.', 404);
        } 
        catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $staff): JsonResponse
    {
        try {
            $this->managementService->deleteStaff($staff);
            return $this->successResponse(null, 'Staff member deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff member not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function restore(int $staff, StaffManagementService $staffService): JsonResponse
    {
        try {
            $restoredStaff = $staffService->restoreStaff($staff);

            return $this->successResponse(
                new StaffProfileResource($restoredStaff),
                'Staff member restored and account activated successfully.',
                200
            );

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff member record not found.', 404);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
    public function filter(Request $request,StaffManagementService $service): JsonResponse
    {
        try {
            // 1. استخراج الفلاتر المسموح بها فقط
            $filters = $request->only([
                'search', 
                'role', 
                'account_status', 
                'attendance_date', 
                'attendance_status', 
                'absence_type', 
                'sort'
            ]);

            // 2. إرسالها للسيرفس
            $staff = $service->filterStaff($filters);

            // 3. إرجاع النتيجة
            return $this->successResponse($staff, 'Staff data filtered successfully.');

        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}