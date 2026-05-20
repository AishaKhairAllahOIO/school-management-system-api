<?php

namespace App\Services\Auth;

use App\Models\SystemAccess;
use App\Mail\SendOtp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SystemAccessService
{
    public function login(array $data)
    {
        $access = SystemAccess::where('email', $data['email'])->first();

        if (!$access || !Hash::check($data['password'], $access->password)) {
            throw ValidationException::withMessages(['البريد او كلمة المرور غير صالحة ']);
        }

        if (!$access->is_active) {
            throw ValidationException::withMessages([ 'الحساب معطل']);
        }

        $otp=(string)random_int(100000,999999);
        Cache::put('otp'.$access->email, $otp,now()->addMinute(10));
        Mail::to($access->email)->send(new sendOtp($otp));

    }

    public function verifyOtp(array $data):array
    {
      $access=SystemAccess::where('email', $data['email'])->first();

      if (!$access) {
        throw ValidationException::withMessages(['email' => 'المسخدم غير موجود']);
    }
      $cacheOtp=Cache::get('otp'.$access->email);
      if (!$cacheOtp || $cacheOtp !== $data['otp'])
        {
          throw ValidationException::withMessages(['رمز التحقق غير صحيح']);
        }
        Cache::forget('otp'.$access->email);

        $tokenExpiration = !empty($data['remember_me']) ? now()->addMonth(1) : now()->addHours(24);
        $token = $access->createToken('system_token',['*'],$tokenExpiration)->plainTextToken;

        return [
            'token' => $token,
            'data' => $access,
        ];
      

    }

public function forgotPassword(array $data)
{
  $access=SystemAccess::where('email', $data['email'])->first();
  if (!$access) {
    throw ValidationException::withMessages(['المسخدم غير موجود']);
  }

  $otp=(string)random_int(100000,999999);
  $expiresAt = time() + 60; 
  Cache::put('reset_otp'.$access->email,$otp,now()->addMinute(10));

  Cache::put('otp_resend_lock' .$access->email, true, now()->addMinute());

  Mail::to($data['email'])->send(new SendOtp($otp));
  return ['remaining_time' => 60];
}

public function verifyOtpForPassword(array $data)
{

   $cachOtp=Cache::get('reset_otp'.$data['email']);
  if (!$cachOtp || $cachOtp !== $data['otp'])
    {
      throw ValidationException::withMessages(['رمز التحقق غير صحيح']);
    }
    $tempToken = bin2hex(random_bytes(20));
    Cache::put('reset_token'.$data['email'], $tempToken, now()->addMinutes(10));
    
    return ['temp_token' => $tempToken];
 
}
public function resetPassword(array $data)
{
  $cachtToken=Cache::get('reset_token'.$data['email']);
  if (!$cachtToken || $cachtToken !== $data['tempToken'])
    {
      throw ValidationException::withMessages(['رمز التحقق غير صحيح']);
    }
    $access = SystemAccess::where('email', $data['email'])->first();
    $access->update(['password' => Hash::make($data['password'])]);  
  
  Cache::forget('reset_token'.$data['email']);
  Cache::forget('reset_otp'.$data['email']);


}



public function logout()
    {
      if (Auth::guard('sanctum')->check()) {
            Auth::guard('sanctum')->user()->currentAccessToken()->delete();
        }   
  // auth('sanctum')->user()->currentAccessToken()->delete(); 
    }
}

