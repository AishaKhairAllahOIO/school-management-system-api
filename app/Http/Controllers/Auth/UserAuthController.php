<?php

namespace App\Http\Controllers\Auth;


use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\Auth\UserVerifyRequest;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    use ApiResource;
    protected OtpService $otpService;


    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
            }


    public function login(UserLoginRequest $request)
    {
        $result = $this->otpService->login($request['phone_number'], $request['password']);

        if (is_string($result)) {
            return $this->errorResponse($result, 400);
        }
        $smsSent = $this->otpService->attemptSendOtp($result['phone_number'], $result['otp']);

        if (!$smsSent) {
            return $this->errorResponse('Failed to send SMS code. Please try again.', 500);
        }

        return $this->successResponse([
            'phone_number' => $result['phone_number']
        ], 'OTP sent successfully. Please check your SMS.', 200);
    }
    public function verifyOtp(UserVerifyRequest $request)
    {

        $result = $this->otpService->verifyOtp($request['phone_number'], $request['otp']);

        if (is_string($result)) {
            return $this->errorResponse($result, 400);
        }

        return $this->successResponse([
            'token' => $result['token'],
            'user' => new UserResource($result['user'])
        ], 'OTP verified successfully. Authentication complete.', 200);
    }
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'digits:10', 'exists:users,phone_number', 'starts_with:09']
        ]);

        $phone_number = $request->input('phone_number');
        $result = $this->otpService->generateOtp($phone_number);

        if (is_string($result)) {
            return $this->errorResponse($result, 400);
        }

        $smsSent = $this->otpService->attemptSendOtp($phone_number, $result['otp']);

        if (!$smsSent) {
            return $this->errorResponse('Failed to resend SMS code. Please try again.', 500);
        }

        return $this->successResponse([
            'phone_number' => $phone_number
        ], 'OTP resent successfully. Please check your SMS.', 200);
    }
    public function logout()
    {
        $this->otpService->logout();
        return $this->successResponse(null, 'Logged out successfully', 200);
    }

}
