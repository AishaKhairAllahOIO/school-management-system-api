<?php

namespace App\Http\Controllers\Auth;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SystemAccessForgetPasswordRequest;
use App\Http\Requests\Auth\SystemAccessLoginRequest;
use App\Http\Requests\Auth\SystemAccessResetPasswordRequest;
use App\Http\Requests\Auth\SystemAccessVerifyRequest;
use App\Http\Resources\Auth\SystemAccessResource;
use App\Http\Resources\Auth\AcademicProfileResource;
use App\Services\Auth\SystemAccessService;
use Illuminate\Validation\ValidationException;
use Exception;
class SystemAccessController extends Controller
{
    use ApiResource;

    public function loginWeb(SystemAccessLoginRequest $request, SystemAccessService $service)
    {
        try {
            $service->loginWeb($request->validated());
            return $this->successResponse(null,'Verification code sent successfully.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
    return $this->errorResponse('An error occurred during login.', 500, ['exception_message' => $e->getMessage()]);
}
    }
    public function loginMobile(SystemAccessForgetPasswordRequest $request, SystemAccessService $service)
    {
        try {
            $service->loginMobile($request->validated());
            return $this->successResponse(null,'Verification code sent successfully.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
    return $this->errorResponse('An error occurred during login.', 500, ['exception_message' => $e->getMessage()]);
}
}

    public function verifyOtpWeb(SystemAccessVerifyRequest $request, SystemAccessService $service)
    {
        try {
            $access = $service->verifyOtpWeb($request->validated());
            $responseData = [
                'user'  => new SystemAccessResource($access['data']),
                'token' => $access['token']
            ];
         return $this->successResponse($responseData,'Logged in successfully.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
         } 
         catch (Exception $e) {
            return $this->errorResponse('OTP verification failed.', 500);
        }
    }
    public function vertifyOtpMobile(SystemAccessVerifyRequest $request,SystemAccessService $service)
    {
          try {
            $access = $service->verifyOtpMobile($request->validated());
            $responseData = [
                'user'  => new AcademicProfileResource($access['data']),
                'token' => $access['token']
            ];
         return $this->successResponse($responseData,'Logged in successfully.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
         } 
         catch (Exception $e) {
            return $this->errorResponse('OTP verification failed.', 500);
        }
    }


    public function forgotPassword(SystemAccessForgetPasswordRequest $request, SystemAccessService $service)
    {
        try {
            $tempToken=$service->forgotPassword($request->validated());
            return $this->successResponse($tempToken,'Password reset code sent to your email.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while processing your request.', 500);
        }
    }

    public function VerifyPassword(SystemAccessVerifyRequest $request, SystemAccessService $service)
    {
        try {
            $token = $service->verifyOtpForPassword($request->validated());
            return $this->successResponse($token, 'Verified successfully. You can now reset your password.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('Verification failed.', 500);
        }
    }



    public function resetPassword(SystemAccessResetPasswordRequest $request, SystemAccessService $service)
    {
        try {
            $service->resetPassword($request->validated());
            return $this->successResponse(null, 'Password reset successfully.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422,$e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update password.', 500);
        }
    }

    
    public function logout(SystemAccessService $service)
    {
        try {
            $service->logout();
            return $this->successResponse(null, 'Logged out successfully.', 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred during logout.', 500);
        }
    }
    
}
