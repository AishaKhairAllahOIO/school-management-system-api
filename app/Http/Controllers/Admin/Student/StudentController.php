<?php

namespace App\Http\Controllers\Admin\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Student\StoreStudentRegisterRequest;
use App\Services\Student\StudentRegisterService;
use Exception;
use App\ApiResource;
class StudentController extends Controller
{
use ApiResource;
    public function store(StudentRegisterService $service,StoreStudentRegisterRequest $request) {
        try{
        $service->registerStudentWithGuardian($request->validated());
        return $this->successResponse(null, 'تم تسجيل الطالب وولي أمره بنجاح.', 201);
        }catch (Exception $e) {
        return $this->errorResponse('حدث خطا اثناء التسجيل', 500, ['exception_message' => $e->getMessage()]);
 
    }
}
}
