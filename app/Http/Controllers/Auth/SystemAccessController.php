<?php

namespace App\Http\Controllers\Auth;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SystemAccessForgetPasswordRequest;
use App\Http\Requests\Auth\SystemAccessLoginRequest;
use App\Http\Requests\Auth\SystemAccessResetPasswordRequest;
use App\Http\Requests\Auth\SystemAccessVerifyRequest;
use App\Http\Resources\Auth\SystemAccessResource;
use App\Services\Auth\SystemAccessService;
use Illuminate\Validation\ValidationException;
use Exception;
class SystemAccessController extends Controller
{
    use ApiResource;

    public function login(SystemAccessLoginRequest $request, SystemAccessService $service)
    {
        try {
            $service->login($request->validated());
            return $this->successResponse(null, 'تم إرسال كود التحقق بنجاح', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
    return $this->errorResponse('حدث خطأ في عملية تسجيل الدخول', 500, ['exception_message' => $e->getMessage()]);
}
    }

    public function verifyOtp(SystemAccessVerifyRequest $request, SystemAccessService $service)
    {
        try {
            $access = $service->verifyOtp($request->validated());
            $responseData = [
                'user'  => new SystemAccessResource($access['data']),
                'token' => $access['token']
            ];
         return $this->successResponse($responseData, 'تم تسجيل الدخول بنجاح', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
         } 
         catch (Exception $e) {
            return $this->errorResponse('فشل التحقق من الكود', 500);
        }
    }

    public function forgotPassword(SystemAccessForgetPasswordRequest $request, SystemAccessService $service)
    {
        try {
            $tempToken=$service->forgotPassword($request->validated());
            return $this->successResponse($tempToken, 'تم إرسال كود استعادة كلمة المرور لبريدك', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء معالجة طلبك', 500);
        }
    }

    public function VerifyPassword(SystemAccessVerifyRequest $request, SystemAccessService $service)
    {
        try {
            $token = $service->verifyOtpForPassword($request->validated());
            return $this->successResponse($token, 'تم التحقق بنجاح، يمكنك تعيين كلمة المرور الآن', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('فشل التحقق', 500);
        }
    }



    public function resetPassword(SystemAccessResetPasswordRequest $request, SystemAccessService $service)
    {
        try {
            $service->resetPassword($request->validated());
            return $this->successResponse(null, 'تم تغيير كلمة المرور بنجاح', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('فشل تحديث كلمة المرور', 500);
        }
    }

    public function logout(SystemAccessService $service)
    {
        try {
            $service->logout();
            return $this->successResponse(null, 'تم تسجيل الخروج بنجاح', 200);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تسجيل الخروج', 500);
        }
    }
}
