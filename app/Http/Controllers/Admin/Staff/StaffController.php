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
use App\Http\Requests\Admin\Student\UpdateGeneralPersonalRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nette\Schema\ValidationException;

class StaffController extends Controller
{
    use ApiResource;

    // حقن السيرفسين معاً بشكل نظيف
    public function __construct(
        private StaffRegisterService $registerService,
        private StaffManagementService $managementService
    ) {}

 public function store(StoreStaffRequest $request): JsonResponse
    {
        try {
            $staff = $this->registerService->registerSingleStaff($request->validated());
            return $this->successResponse(new StaffProfileResource($staff), 'تم تسجيل الموظف بنجاح.', 201);
        }  catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء التسجيل.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function importExcel(ImportExalSheetStudentRequest $request): JsonResponse
    {
        try {
            $batch = $this->registerService->initiateStaffExcelImport($request->file('excel_file'), $request->user()->id);
            return $this->successResponse(['batch_id' => $batch->id], 'تم استلام الملف بنجاح، جاري معالجة بيانات الموظفين في الخلفية.', 202);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء رفع الملف.', 500, ['error' => $e->getMessage()]);
        }
    }
        public function getImportStatus($batch)
    {
       $batch = ImportBatch::find($batch); 

     if(!$batch) {
            return $this->errorResponse('لا يوجد ملف كهذا', 404); // يُفضل كود 404 وليس 422
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
        ], 'تم جلب حالة الحزمة بنجاح.');
    }
public function exportErrors(ImportBatch $batch, StaffRegisterService $service)
    {
        try{
        return $service->downloadBatchErrors($batch);
        }
        catch(Exception $e)
        {
            return $this->successResponse(null,'No error to show',200);
        }
    }
    // ==========================================
    // 2. إدارة الموظفين (CRUD & Management)
    // ==========================================

    public function index(): JsonResponse
    {
        try {
            $staff = $this->managementService->getAllStaff();
            return $this->successResponse(StaffProfileResource::collection($staff), 'تم جلب بيانات الموظفين بنجاح.');
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب قائمة الموظفين.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show(int $staff): JsonResponse
    {
        try {
            $staffData = $this->managementService->getStaffById($staff);
            return $this->successResponse(new StaffProfileResource($staffData), 'تم جلب تفاصيل الموظف بنجاح.');
        }catch(ModelNotFoundException $e) 
        {
            return $this->errorResponse('الموظف غير موحود',404);
        }
        catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء عرض بيانات الموظف.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updatePersonal(UpdateGeneralPersonalRequest $request, int $staff): JsonResponse
    {
        try {
            $updated = $this->managementService->updatePersonalData($staff, $request->validated());
            return $this->successResponse(new StaffProfileResource($updated), 'تم تحديث البيانات الشخصية للموظف بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('المستخدم غير مموجود ', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث البيانات الشخصية.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updateEmployment(UpdateEmploymentStaffRequest $request, int $staff): JsonResponse
    {
        try {
            $updated = $this->managementService->updateEmploymentData($staff, $request->validated());
            return $this->successResponse(new StaffProfileResource($updated), 'تم تحديث البيانات الوظيفية للموظف بنجاح.');
        } 
        catch (ModelNotFoundException $e) {
            return $this->errorResponse('المستخدم غير مموجود ', 404);
        }
        catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث البيانات الوظيفية.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $fullName = $request->query('name');
            if (empty($fullName)) {
                return $this->errorResponse('يرجى تحديد اسم للبحث عنه.', 422);
            }
            $perPage = $request->query('per_page', 15);
            $results = $this->managementService->searchStaffByFullName($fullName, $perPage);
            return $this->successResponse($results, 'تم جلب نتائج البحث بنجاح.');
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إجراء عملية البحث.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function alphabetical(Request $request): JsonResponse
    {
        try {
            // الكنترولر يمرر المتغيرات كما هي، والسيرفس هو من يفلتر اللوجيك الخاطئ!
            $direction = $request->query('direction', 'asc');
            $perPage = $request->query('per_page', 15);

            $staff = $this->managementService->getAllStaffAlphabetically($direction, $perPage);
            return $this->successResponse($staff, 'تم جلب الموظفين مرتبين أبجدياً بنجاح.');
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء ترتيب الموظفين أبجدياً.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function toggleStatus(int $staff): JsonResponse
    {
        try {
            // السيرفس يقوم بالتبديل ويعيد النص الجاهز (مفعل/معطل)
            $statusText = $this->managementService->toggleAccountStatus($staff);
            return $this->successResponse(null, "تم تغيير حالة حساب الموظف بنجاح إلى: {$statusText}.");
        }catch (ModelNotFoundException $e) {
            return $this->errorResponse('المستخدم غير مموجود ', 404);
        } 
        catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تبديل حالة الحساب.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $staff): JsonResponse
    {
        try {
            $this->managementService->deleteStaff($staff);
            return $this->successResponse(null, 'تم نقل بيانات الموظف إلى سلة المهملات بنجاح.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('المستخدم غير مموجود ', 404);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء محاولة حذف الموظف.', 500, ['error' => $e->getMessage()]);
        }
    }
}