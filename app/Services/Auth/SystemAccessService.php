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
            throw ValidationException::withMessages(['invalid email or password']);
        }

        if (!$access->is_active) {
            throw ValidationException::withMessages(['unactive account']);
        }

        $otp=(string)random_int(100000,999999);
        Cache::put('otp'.$access->email, $otp,now()->addMinute(10));
        Mail::to($access->email)->send(new sendOtp($otp));

    }

    public function verifyOtp(array $data):array
    {
      $access=SystemAccess::where('email', $data['email'])->first();
      $cacheOtp=Cache::get('otp'.$access->email);
      if (!$cacheOtp || $cacheOtp !== $data['otp'])
        {
          throw ValidationException::withMessages(['incorrect otp']);
        }
        Cache::forget('otp'.$access->email);
        $tokenExpiration = !empty($data['remember_me']) ? now()->addMonth(1) : now()->addHours(24);
        $token = $access->createToken('system_token',['*'],$tokenExpiration)->plainTextToken;

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

