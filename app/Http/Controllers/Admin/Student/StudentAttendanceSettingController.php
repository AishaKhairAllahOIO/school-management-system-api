<?php

namespace App\Http\Controllers\Admin\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Student\StudentAttendanceSettingResource;
use App\Http\Requests\Admin\Student\UpdateStudentAttendanceSettingRequest;
use App\Http\Requests\Admin\Student\StoreStudentAttendanceSettingRequest;
use App\Services\Student\StudentAttendanceSettingsService;
use Exception;
use App\Models\StudentAttendanceSetting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ApiResource;
class StudentAttendanceSettingController extends Controller
{
       use ApiResource;

    protected StudentAttendanceSettingsService $service;
    public function __construct(StudentAttendanceSettingsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $settings = $this->service->getAllSettings();
        
        return $this->successResponse(
            StudentAttendanceSettingResource::collection($settings),
            'تم جلب الإعدادات بنجاح'
        );
    }

    // public function show(int $id)
    // {
    //     $setting = StudentAttendanceSetting::findOrFail($id);
        
    //     return $this->successResponse(
    //         new StudentAttendanceSettingResource($setting),
    //         'تم جلب الإعداد بنجاح'
    //     );
    // }

    public function getBySemester(int $semesterId)
    {
        try{
         $setting = $this->service->getSettingsBySemester($semesterId);
         
         return $this->successResponse(
             new StudentAttendanceSettingResource($setting),
             'تم جلب إعدادات الفصل بنجاح'
         );
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse(
                'لا توجد إعدادات لهذا الفصل الدراسي.',
                404 // 404 Not Found
            );
        }
        catch(Exception $e)
        {
            return $this->errorResponse(
                'حدث خطأ أثناء جلب إعدادات الفصل الدراسي.',
                500 // 500 Internal Server Error
            );
        }
    }

    public function store(StoreStudentAttendanceSettingRequest $request)
    { try{
        $setting = $this->service->createSettings($request->validated());
        
        return $this->successResponse(
            new StudentAttendanceSettingResource($setting),
            'تم إنشاء إعدادات الحضور بنجاح',
            201 // 201 Created
        );
    }catch(Exception $e)
        {
            return $this->errorResponse(
                'حدث خطأ أثناء إنشاء إعدادات الحضور.',
                500 ,$e->getMessage()// 500 Internal Server Error
            );
        }
    }

    public function update(UpdateStudentAttendanceSettingRequest $request, int $id)
    {
        try{
        $setting = $this->service->updateSettings($id, $request->validated());
        
        return $this->successResponse(
            new StudentAttendanceSettingResource($setting),
            'تم تعديل إعدادات الحضور بنجاح'
        );}
        catch(ModelNotFoundException $e)
        {
            return $this->errorResponse(
                'الإعدادات غير موجودة.',
                404 // 404 Not Found
            );
        }
        catch(Exception $e)
        {
            return $this->errorResponse(
                'حدث خطأ أثناء تعديل إعدادات الحضور.',
                500 // 500 Internal Server Error
            );
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->deleteSettings($id);
            
            return $this->successResponse(
                null,
                'تم حذف الإعدادات بنجاح'
            );
            
        }catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'الإعدادات غير موجودة.',
                404 // 404 Not Found
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                403 // 403 Forbidden
            );
        }
    }
    }
