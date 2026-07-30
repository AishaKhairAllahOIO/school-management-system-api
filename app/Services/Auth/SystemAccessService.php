<?php

namespace App\Services\Auth;

use App\Models\SystemAccess;
use App\Mail\SendOtp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SystemAccessService
{
    public function loginWeb(array $data)
    {
        $access = User::where('email', $data['email'])->first();

        if (!$access || !Hash::check($data['password'], $access->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.'
            ]);
        }
        if ($access->record_status !== 'active') {
            throw ValidationException::withMessages([
                'email' => 'This account is no longer active.'
            ]);
        }
        if ($access->account_status == 'disabled') {
            throw ValidationException::withMessages([
                'email' => 'This account is disabled. Please contact administration.'
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put('otp' . $access->email, $otp, now()->addMinutes(10));

        Mail::to($access->email)->send(new sendOtp($otp));
    }

    public function loginMobile(array $data)
    {
        $access = User::where('email', $data['email'])->first();

        if (!$access) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email '
            ]);
        }
        if ($access->record_status !== 'active') {
            throw ValidationException::withMessages([
                'email' => 'This account is no longer active.'
            ]);
        }
        if ($access->account_status == 'disabled') {
            throw ValidationException::withMessages([
                'email' => 'This account is disabled. Please contact administration.'
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put('otp' . $access->email, $otp, now()->addMinutes(10));

        Mail::to($access->email)->send(new sendOtp($otp));
    }



    public function verifyOtpWeb(array $data): array
    {
        $access = User::where('email', $data['email'])->first();
        if (!$access) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email.'
            ]);
        }
        $cacheOtp = Cache::get('otp' . $data['email']);
        //     dd([
//     'In_Cache' => $cacheOtp,
//     'In_Cache_Type' => gettype($cacheOtp),
//     'From_Request' => $data['otp'],
//     'From_Request_Type' => gettype($data['otp']),
//     'Is_Equal' => ($cacheOtp == $data['otp'])
// ]);
        if (!$cacheOtp || (string) $cacheOtp !== (string) $data['otp']) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP.'
            ]);
        }

        $tokenExpiration = !empty($data['remember_me']) ? now()->addMonth(1) : now()->addHours(24);
        $token = $access->createToken('system_token', ['*'], $tokenExpiration)->plainTextToken;
        // $access->account_status='enabled';

        Cache::forget('otp' . $access->email);

        return [
            'token' => $token,
            'data' => $access,
        ];
    }
    public function verifyOtpMobile(array $data)
    {
        $access = User::where('email', $data['email'])->first();
        if (!$access) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email.'
            ]);
        }
        $cacheOtp = Cache::get('otp' . $data['email']);
        if (!$cacheOtp || (string) $cacheOtp !== (string) $data['otp']) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP.'
            ]);
        }
        $token = $access->createToken('system_token', ['*'], now()->addYear(1))->plainTextToken;
        //$access->account_status='enabled';
        Cache::forget('otp' . $access->email);

        return [
            'token' => $token,
            'data' => $access,
        ];
    }

    public function forgotPassword(array $data)
    {
        $access = User::where('email', $data['email'])->first();
        if (!$access) {
            throw ValidationException::withMessages(['email' => 'User not found in the system.']);
        }

        if (Cache::has('otp_resend_lock' . $access->email)) {
            throw ValidationException::withMessages([
                'email' => 'Please wait a minute before requesting a new OTP.'
            ]);
        }
        $otp = (string) random_int(100000, 999999);
        Cache::put('reset_otp' . $access->email, $otp, now()->addMinutes(10));

        Cache::put('otp_resend_lock' . $access->email, true, now()->addMinute());
        Mail::to($data['email'])->send(new SendOtp($otp));

        return ['remaining_time' => 60];
    }

    public function verifyOtpForPassword(array $data)
    {

        $cachOtp = Cache::get('reset_otp' . $data['email']);
        if (!$cachOtp || $cachOtp !== $data['otp']) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP.'
            ]);
        }
        Cache::forget('reset_otp' . $data['email']);
        $tempToken = bin2hex(random_bytes(20));
        Cache::put('reset_token' . $data['email'], $tempToken, now()->addMinutes(10));

        return ['temp_token' => $tempToken];

    }

    public function resetPassword(array $data)
    {
        $cachtToken = Cache::get('reset_token' . $data['email']);

        if (!$cachtToken || $cachtToken !== $data['tempToken']) {
            throw ValidationException::withMessages([
                'tempToken' => 'Invalid or expired reset token.'
            ]);
        }

        $access = User::where('email', $data['email'])->first();
        $access->update(['password' => Hash::make($data['password'])]);

        // تنظيف الكاش
        Cache::forget('reset_token' . $data['email']);
        Cache::forget('reset_otp' . $data['email']);

        $token = $access->createToken('system_token', ['*'], now()->addHours(24))->plainTextToken;

        return [
            'token' => $token,
            'data' => $access,
        ];
    }



    public function logout()
    {
        if (Auth::guard('sanctum')->check()) {
            Auth::guard('sanctum')->user()->currentAccessToken()->delete();
        }
        // auth('sanctum')->user()->currentAccessToken()->delete();
    }
}

