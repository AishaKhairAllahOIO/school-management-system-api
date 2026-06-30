<?php

namespace App\Http\Controllers\Admin\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Student\StoreStudentRegisterRequest;
use App\Services\Student\StudentRegisterService;
use App\Http\Requests\Admin\Student\ImportExalSheetStudentRequest;
use App\Http\Requests\Admin\Student\GetBatchesHistoryExalFilesRequest;
use App\Http\Requests\Admin\Student\UpdateStudentRequest;
use App\Services\Student\StudentManagementService;
use App\Http\Resources\Student\StudentEnrollmentResource;

use App\Models\ImportBatch;
use Exception;
use App\ApiResource;
use App\Http\Requests\Admin\Student\IndexStudentRequest;

class StudentController extends Controller
{
use ApiResource;
    public function store(StudentRegisterService $service,StoreStudentRegisterRequest $request) {
        try{
        $data=$service->registerStudentWithGuardian($request->validated());
        return $this->successResponse(new StudentEnrollmentResource($data), 'تم تسجيل الطالب وولي أمره بنجاح.', 201);
        }catch (Exception $e) {
        return $this->errorResponse('حدث خطا اثناء التسجيل', 500, ['exception_message' => $e->getMessage()]);
 
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
            'has_errors'      => $batch->failed_rows > 0,
        ], 'تم جلب حالة الحزمة بنجاح.');
    }
    /**
     * FR-05 (Ledger): جلب سجل الأرشيف التاريخي لعمليات الرفع مع الفلترة والبحث
     */
    public function getBatchesHistory(GetBatchesHistoryExalFilesRequest $request, StudentRegisterService $service) 
    {        $batches = $service->getImportBatchesArchive($request->validated());

        return $this->successResponse($batches, 'تم جلب الأرشيف التاريخي لعمليات الرفع بنجاح.');
    } 
    /**
     * FR-09, FR-10, FR-12, FR-13: جلب وتصفية والبحث في سجلات الطلاب
     */
    public function index(IndexStudentRequest $request, StudentManagementService $service)
    {
        $students = $service->getAllStudents($request->all());

        return $this->successResponse($students, 'تم جلب سجلات الطلاب بنجاح.');
    }

    /**
     * FR-11: عرض الملف الشخصي المفصل للطالب
     */
    public function show($id, StudentManagementService $service)
    {
        $student = $service->getStudentById($id);

        return $this->successResponse($student, 'تم جلب بيانات الطالب بنجاح.');
    }

    /**
     * FR-06: التعديل على بيانات الطالب الأساسية
     */
    public function update(UpdateStudentRequest $request, $id, StudentManagementService $service)
    {
        $student = $service->updateStudent($id, $request->validated());

        return $this->successResponse($student, 'تم تعديل بيانات الطالب بنجاح.');
    }

    /**
     * FR-07: شطب طالب من المدرسة (Soft أو Hard حسب السياسة)
     */
    public function destroy($id, StudentManagementService $service)
    {
        $service->deleteStudent($id);

        return $this->successResponse(null, 'تم شطب الطالب من النظام بنجاح.');
    }

    /**
     * FR-08: تجميد أو إعادة تفعيل حساب الطالب (للبوابة المالية أو الدخول)
     */
    // public function toggleStatus($id, StudentManagementService $service)
    // {
    //     $newStatus = $service->toggleAccountStatus($id);

    //     return $this->successResponse(['record_status' => $newStatus], 'تم تغيير حالة القيد بنجاح.');
    // } 
}
