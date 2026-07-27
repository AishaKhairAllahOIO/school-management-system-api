<?php

namespace App\Http\Controllers\Admin\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Student\StoreStudentRegisterRequest;
use App\Http\Resources\Student\StudentEnrollmentResource;
use App\Http\Resources\User\BaseUserProfileResource;
use App\Services\Student\StudentRegisterService;
use App\Http\Requests\Admin\Student\ImportExalSheetStudentRequest;
use App\Http\Requests\Admin\Student\GetBatchesHistoryExalFilesRequest;
use App\Services\Student\StudentManagementService;
use App\Http\Resources\Student\StudentProfileWithEnrollmentResource;
use App\Http\Resources\Student\StudentProfileResource;
use App\Http\Requests\Admin\Student\UpdateEnrollmentRequest;
use App\Http\Resources\Student\StudentFilterResource;
use App\Http\Requests\Admin\Student\IndexStudentRequest;
use App\Http\Requests\Admin\Student\UpdateGuardianPersonalDataRequest;
use App\Http\Requests\Admin\Student\UpdateStudentPersonalDataRequest;
use App\Models\ImportBatch;
use App\ApiResource;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentController extends Controller
{
use ApiResource;


    public function store(StudentRegisterService $service,StoreStudentRegisterRequest $request) {
        try{
        $data=$service->registerStudentWithGuardian($request->validated());
        return $this->successResponse(new StudentProfileWithEnrollmentResource($data), 'تم تسجيل الطالب وولي أمره بنجاح.', 201);
        }catch (Exception $e) {
        return $this->errorResponse('حدث خطا اثناء التسجيل', $e->getCode(), ['exception_message' => $e->getMessage()]);
 
    }
}
public function importExcel(ImportExalSheetStudentRequest $request,StudentRegisterService $service)
    {
        $batch = $service->initiateExcelImport(
            $request->file('excel_file'),
            $request->user()->id
        );

        return $this->successResponse(
            ['batch_id' => $batch->id], 
            'تم استلام الملف بنجاح، جاري معالجة البيانات في الخلفية', 
            202
        );
    }
public function exportErrors(ImportBatch $batch, StudentRegisterService $service)
    {
        try{
        return $service->downloadBatchErrors($batch);
        }
        catch(Exception $e)
        {
            return $this->successResponse(null,'No error to show',200);
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

    public function getBatchesHistory(GetBatchesHistoryExalFilesRequest $request, StudentRegisterService $service) 
    {        $batches = $service->getImportBatchesArchive($request->validated());

        return $this->successResponse($batches, 'تم جلب الأرشيف التاريخي لعمليات الرفع بنجاح.');
    } 





 public function filter(IndexStudentRequest $request,StudentManagementService $service)
    {
        $students = $service->filterStudents($request->validated());

        return $this->successResponse(
            StudentFilterResource::collection($students)->response()->getData(true), 
            'تم جلب سجلات الطلاب المفلترة بنجاح.'
        );
    }


    public function search(Request $request,StudentManagementService $service)
    {
        // إجبار الفرونت إند على إرسال حرفين على الأقل للبحث لعدم إرهاق الداتابيز
        $request->validate([
            'q' => 'required|string|min:2' 
        ]);

        $students = $service->searchStudents($request->q);

        return $this->successResponse(
            StudentFilterResource::collection($students)->response()->getData(true), 
            'تم جلب نتائج البحث بنجاح.'
        );
    }


    public function show($id, StudentManagementService $service)
    {
        try{
        $student = $service->getStudentPersonalProfile($id);

        return $this->successResponse(new StudentProfileResource($student), 'تم جلب بيانات الطالب بنجاح.');
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
    public function showFullProfile($enrollmentId, StudentManagementService $service)
    {
        try{
        $enrollment = $service->getStudentFullProfile($enrollmentId);

        return $this->successResponse(new StudentProfileWithEnrollmentResource($enrollment), 'تم جلب بيانات الطالب بنجاح.');
        }catch(Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

   public function updatePersonal(UpdateStudentPersonalDataRequest $request, int $student,StudentManagementService $studentService)
    {
        try{
        $updatedStudent = $studentService->updateStudentPersonalData($student, $request->validated());

        return $this->successResponse(new StudentProfileWithEnrollmentResource($updatedStudent), 'تم تحديث بيانات الطالب بنجاح.',201);
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse('المستخدم غير موجود',404);
        }
        catch(Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    // public function updateEnrollment(UpdateEnrollmentRequest $request, int $enrollmentId,StudentManagementService $studentService)
    // {
    //     try{
    //     $updatedEnrollment = $studentService->updateEnrollmentData($enrollmentId, $request->validated());

    //     return $this->successResponse(new StudentEnrollmentResource($updatedEnrollment), 'تم تحديث بيانات القيد الأكاديمي بنجاح.',201);
    //     }catch(ModelNotFoundException $e)
    //     {
    //         return $this->errorResponse('السجل غير موجود');
    //     }
    //     catch(Exception $e){
    //         return $this->errorResponse($e->getMessage(), 404);
    //     }
    // }


    public function updateGuardian(UpdateGuardianPersonalDataRequest $request,int  $guardianId,StudentManagementService $studentService)
    {
        try{
        $updatedGuardian = $studentService->updateGuardianPersonalData($guardianId, $request->validated());

        return $this->successResponse(new BaseUserProfileResource($updatedGuardian), 'تم تحديث بيانات ولي الأمر بنجاح.',201);
    
        }
        catch(ModelNotFoundException $e)
        {
            return $this->errorResponse('المستخدم غير موجود',404);
        }
        catch(Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
        }

    /**
     * FR-07: شطب طالب من المدرسة (Soft أو Hard حسب السياسة)
     */
    public function destroy(int $id, StudentManagementService $service)
    {
        try{
        $service->deleteStudent($id);

        return $this->successResponse(null, 'تم شطب الطالب من النظام بنجاح.');
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse('تسجيل الطالب  غير موجود',404);
        }
        catch(Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }


    public function toggleAccountStatus($id, StudentManagementService $service)
    {
        try{
        $newStatus = $service->toggleAccountStatus($id);

        return $this->successResponse(['account_status' => $newStatus], 'تم تغيير حالة القيد بنجاح.');
        }catch(Exception $e)
        {
            return $this->errorResponse($e->getMessage(), 404);
        }
    } 
}
