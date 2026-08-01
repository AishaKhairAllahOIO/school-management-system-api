<?php

namespace App\Services\Auth;

use App\Mail\SendOtp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SystemAccessService
{
    /**
     * Generate, cache and send an OTP for either login verification
     * or password reset.
     */
    private function sendOtpForPurpose(User $user, string $purpose): array
    {
        $otp = (string) random_int(100000, 999999);

        $otpKey = match ($purpose) {
            'login' => 'otp' . $user->email,
            'password_reset' => 'reset_otp' . $user->email,
            default => throw ValidationException::withMessages([
                'purpose' => ['The OTP purpose is invalid.'],
            ]),
        };

        $lockKey = match ($purpose) {
            'login' => 'login_otp_resend_lock' . $user->email,
            'password_reset' => 'otp_resend_lock' . $user->email,
            default => throw ValidationException::withMessages([
                'purpose' => ['The OTP purpose is invalid.'],
            ]),
        };

        Cache::put(
            $otpKey,
            $otp,
            now()->addMinutes(10)
        );

        Cache::put(
            $lockKey,
            true,
            now()->addMinute()
        );

        Mail::to($user->email)->send(
            new SendOtp($otp)
        );

        return [
            'remaining_time' => 60,
        ];
    }

    public function loginWeb(array $data): array
    {
        $access = User::where('email', $data['email'])->first();

        if (!$access || !Hash::check($data['password'], $access->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        if ($access->record_status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account is no longer active.'],
            ]);
        }

        if ($access->account_status === 'disabled') {
            throw ValidationException::withMessages([
                'email' => [
                    'This account is disabled. Please contact administration.',
                ],
            ]);
        }

        /*
         * Proves that the user passed the email/password step.
         * Login OTP resend is allowed only while this challenge exists.
         */
        Cache::put(
            'login_otp_challenge' . $access->email,
            true,
            now()->addMinutes(15)
        );

        return $this->sendOtpForPurpose(
            $access,
            'login'
        );
    }

    public function loginMobile(array $data)
    {
        $access = User::where('email', $data['email'])->first();

        if (!$access) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email.'],
            ]);
        }

        if ($access->record_status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account is no longer active.'],
            ]);
        }

        if ($access->account_status === 'disabled') {
            throw ValidationException::withMessages([
                'email' => [
                    'This account is disabled. Please contact administration.',
                ],
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        Cache::put(
            'otp' . $access->email,
            $otp,
            now()->addMinutes(10)
        );

        Mail::to($access->email)->send(
            new SendOtp($otp)
        );
    }

    public function verifyOtpWeb(array $data): array
    {
        $access = User::where('email', $data['email'])->first();

        if (!$access) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email.'],
            ]);
        }

        $cacheOtp = Cache::get(
            'otp' . $data['email']
        );

        if (
            !$cacheOtp ||
            (string) $cacheOtp !== (string) $data['otp']
        ) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $tokenExpiration = !empty($data['remember_me'])
            ? now()->addMonth()
            : now()->addHours(24);

        $token = $access
            ->createToken(
                'system_token',
                ['*'],
                $tokenExpiration
            )
            ->plainTextToken;

        Cache::forget(
            'otp' . $access->email
        );

        Cache::forget(
            'login_otp_challenge' . $access->email
        );

        Cache::forget(
            'login_otp_resend_lock' . $access->email
        );

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
                'email' => ['Invalid email.'],
            ]);
        }

        $cacheOtp = Cache::get(
            'otp' . $data['email']
        );

        if (
            !$cacheOtp ||
            (string) $cacheOtp !== (string) $data['otp']
        ) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $token = $access
            ->createToken(
                'system_token',
                ['*'],
                now()->addYear()
            )
            ->plainTextToken;

        Cache::forget(
            'otp' . $access->email
        );

        return [
            'token' => $token,
            'data' => $access,
        ];
    }

    /**
     * Handles both:
     * - Initial/reset password OTP request.
     * - Login OTP resend.
     *
     * Expected purpose:
     * - password_reset
     * - login
     */
    public function forgotPassword(array $data): array
    {
        $access = User::where('email', $data['email'])->first();

        if (!$access) {
            throw ValidationException::withMessages([
                'email' => ['User not found in the system.'],
            ]);
        }

        $purpose = $data['purpose'] ?? 'password_reset';

        if ($purpose === 'login') {
            /*
             * Do not allow login OTP resend unless loginWeb()
             * already validated the email and password.
             */
            if (
                !Cache::has(
                    'login_otp_challenge' . $access->email
                )
            ) {
                throw ValidationException::withMessages([
                    'email' => [
                        'Your login verification session has expired. Please sign in again.',
                    ],
                ]);
            }

            if (
                Cache::has(
                    'login_otp_resend_lock' . $access->email
                )
            ) {
                throw ValidationException::withMessages([
                    'email' => [
                        'Please wait a minute before requesting another verification code.',
                    ],
                ]);
            }

            if ($access->record_status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => [
                        'This account is no longer active.',
                    ],
                ]);
            }

            if ($access->account_status === 'disabled') {
                throw ValidationException::withMessages([
                    'email' => [
                        'This account is disabled. Please contact administration.',
                    ],
                ]);
            }

            return $this->sendOtpForPurpose(
                $access,
                'login'
            );
        }

        if ($purpose !== 'password_reset') {
            throw ValidationException::withMessages([
                'purpose' => ['The OTP purpose is invalid.'],
            ]);
        }

        if (
            Cache::has(
                'otp_resend_lock' . $access->email
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Please wait a minute before requesting a new OTP.',
                ],
            ]);
        }

        return $this->sendOtpForPurpose(
            $access,
            'password_reset'
        );
    }

    public function verifyOtpForPassword(array $data)
    {
        $cacheOtp = Cache::get(
            'reset_otp' . $data['email']
        );

        if (
            !$cacheOtp ||
            (string) $cacheOtp !== (string) $data['otp']
        ) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        Cache::forget(
            'reset_otp' . $data['email']
        );

        $tempToken = bin2hex(
            random_bytes(20)
        );

        Cache::put(
            'reset_token' . $data['email'],
            $tempToken,
            now()->addMinutes(10)
        );

        return [
            'temp_token' => $tempToken,
        ];
    }

    public function resetPassword(array $data)
    {
        $cachedToken = Cache::get(
            'reset_token' . $data['email']
        );

        if (
            !$cachedToken ||
            $cachedToken !== $data['tempToken']
        ) {
            throw ValidationException::withMessages([
                'tempToken' => [
                    'Invalid or expired reset token.',
                ],
            ]);
        }

        $access = User::where(
            'email',
            $data['email']
        )->first();

        if (!$access) {
            throw ValidationException::withMessages([
                'email' => ['User not found in the system.'],
            ]);
        }

        $access->update([
            'password' => Hash::make(
                $data['password']
            ),
        ]);

        Cache::forget(
            'reset_token' . $data['email']
        );

        Cache::forget(
            'reset_otp' . $data['email']
        );

        Cache::forget(
            'otp_resend_lock' . $data['email']
        );

        $token = $access
            ->createToken(
                'system_token',
                ['*'],
                now()->addHours(24)
            )
            ->plainTextToken;

        return [
            'token' => $token,
            'data' => $access,
        ];
    }

    public function logout()
    {
        if (Auth::guard('sanctum')->check()) {
            Auth::guard('sanctum')
                ->user()
                ->currentAccessToken()
                ?->delete();
        }
    }
}
