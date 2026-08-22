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
            'Student attendance settings retrieved successfully.'
        );
    }

    // public function show(int $id)
    // {
    //     $setting = StudentAttendanceSetting::findOrFail($id);
        
    //     return $this->successResponse(
    //         new StudentAttendanceSettingResource($setting),
    //         'Student attendance setting retrieved successfully.'
    //     );
    // }

    public function getBySemester(int $semesterId)
    {
        try{
         $setting = $this->service->getSettingsBySemester($semesterId);
         
         return $this->successResponse(
             new StudentAttendanceSettingResource($setting),
             'Student attendance settings retrieved successfully.'
         );
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse(
                'No attendance settings found for the specified semester.',
                404 // 404 Not Found
            );
        }
        catch(Exception $e)
        {
            return $this->errorResponse(
                'Error:Server',
                500 // 500 Internal Server Error
            );
        }
    }

    public function store(StoreStudentAttendanceSettingRequest $request)
    { try{
        $setting = $this->service->createSettings($request->validated());
        
        return $this->successResponse(
            new StudentAttendanceSettingResource($setting),
            'Student attendance settings created successfully.',
            201 // 201 Created
        );
    }catch(Exception $e)
        {
            return $this->errorResponse(
                'Error:Server',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function update(UpdateStudentAttendanceSettingRequest $request, int $id)
    {
        try{
        $setting = $this->service->updateSettings($id, $request->validated());
        
        return $this->successResponse(
            new StudentAttendanceSettingResource($setting),
            'Student attendance settings updated successfully.'
        );}
        catch(ModelNotFoundException $e)
        {
            return $this->errorResponse(
                'Student attendance setting not found.',
                404 // 404 Not Found
            );
        }
        catch(Exception $e)
        {
            return $this->errorResponse(
                'Error:Server',
                $e->getCode() // 500 Internal Server Error
            ,$e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->deleteSettings($id);
            
            return $this->successResponse(
                null,
                'Student attendance settings deleted successfully.'
            );
            
        }catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Student attendance settings not found.',
                404 // 404 Not Found
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error:Server',
                500,
                403 // 403 Forbidden
            );
        }
    }
    }
