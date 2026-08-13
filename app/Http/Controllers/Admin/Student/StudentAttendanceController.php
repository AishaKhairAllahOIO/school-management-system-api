<?php

namespace App\Http\Controllers\Admin\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Student\StudentAttendanceService;
use App\Http\Requests\Admin\Student\StoreBulkStudentAttendanceRequest;
use Throwable; // 👈 كما تفعلين دائماً
use App\Http\Resources\Student\StudentAttendanceResource;
use App\Http\Requests\Admin\Student\UpdateSingleAttendanceRequest;
use App\ApiResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentAttendanceController extends Controller
{
    use ApiResource;

    protected StudentAttendanceService $service;

    public function __construct(StudentAttendanceService $service)
    {
        $this->service = $service;
    }

    public function storeBulk(StoreBulkStudentAttendanceRequest $request)
    {
        try {
            $this->service->storeBulkAttendance($request->validated());
            
            return $this->successResponse(
                null, 
                'تم تسجيل الغياب والحضور وإرسال الإشعارات بنجاح.', 
                201
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء تسجيل الحضور.', 
                500, 
                ['exception_message' => $e->getMessage()]
            );
        }
    }
    public function get(int $id)
    {
        try {
            $result = $this->service->getRecord($id);

            $responseData = [
                'attendance_record'  => new StudentAttendanceResource($result['record']),
                'attendance_summary' => $result['attendance_summary'],
            ];

            return $this->successResponse(
                $responseData,
                'تم جلب سجل حضور الطالب وتفاصيل العدادات بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('سجل حضور الطالب غير موجود.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse(
                'حدث خطأ أثناء جلب سجل حضور الطالب.',
                500,
                ['exception_message' => $e->getMessage()]
            );
        }
    }    public function index(Request $request)
        {
            try {
                $request->validate([
                    'search_name'     => 'nullable|string',
                    'grade_id'        => 'nullable|integer|exists:grade_levels,id',
                    'class_room_id'   => 'nullable|integer|exists:class_rooms,id',
                    'attendance_date' => 'nullable|date',
                    'status'          => 'nullable|in:present,absent',
                    'absence_type'    => 'nullable|in:excused,unexcused',
                    'semester_id' => 'nullable|exists:semesters,id',
                ]);

                $result = $this->service->filterStudentsAttendance($request->all());
                return $this->successResponse($result, 'تم جلب سجلات حضور الطلاب وتفاصيل التفقد بنجاح.');
            } catch (\Throwable $e) {
                return $this->errorResponse('حدث خطأ أثناء تصفية سجلات حضور الطلاب.', 500, ['error' => $e->getMessage()]);
            }
        }
  
    public function update(UpdateSingleAttendanceRequest $request, int $id)
{
    try {
        // 1. $result هنا عبارة عن مصفوفة ['record' => ..., 'attendance_summary' => ...]
        $result = $this->service->updateSingleAttendance($id, $request->validated());
        
        // 2. نمرر الموديل فقط (record) إلى الريسورس حتى لا يحدث خطأ الـ array
        $resource = new StudentAttendanceResource($result['record']);
        
        // 3. نجهز البيانات النهائية للريسبونس
        $responseData = [
            'attendance_record' => $resource,
            'attendance_summary' => $result['attendance_summary']
        ];

        return $this->successResponse($responseData, 'تم تعديل سجل حضور الطالب وإعادة تصفية العدادات بنجاح.', 200);

    } catch (ModelNotFoundException $e) {
        return $this->errorResponse('سجل حضور الطالب غير موجود.', 404);
    } catch (\Throwable $e) {
        return $this->errorResponse('حدث خطأ أثناء تعديل سجل حضور الطالب.', 500, ['error' => $e->getMessage()]);
    }
}

 

 public function destroy(int $id)
    {
        try {
            $this->service->deleteSingleAttendance($id);
            return $this->successResponse(null, 'تم حذف سجل حضور الطالب وإعادة تصفية العدادات بنجاح.');
        }catch(ModelNotFoundException $e) {
            return $this->errorResponse('سجل حضور الطالب غير موجود.', 404);
        } 
        catch (\Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف سجل حضور الطالب.', 500, ['error' => $e->getMessage()]);
        }
    }
}
