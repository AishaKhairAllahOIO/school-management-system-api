<?php

namespace App\Services\Auth;

use App\ApiResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    use ApiResource;

    private string $apiKey;
    private string $apiUrl;
    private string $appleReviewPhone;
    private string $appleStaticOtp;

    // تم إزالة UserService لأنه غير مستخدم

    public function __construct()
    {
        $this->apiKey = config('services.traccar.api_key');
        $this->apiUrl = config('services.traccar.api_url');
        $this->appleReviewPhone = config('services.traccar.apple_review_phone');
        $this->appleStaticOtp = config('services.traccar.apple_static_otp');
    }

    public function login(string $phone_number): array|string
    {
        $user = User::where('phone_number', $phone_number)->first();

        if (!$user) {
            Log::channel('single')->warning('[LOGIN] Attempt failed. User not found.', ['phone_number' => $phone_number]);
            throw new HttpResponseException($this->errorResponse('Invalid credentials', 422));
        }

        if ($user->record_status !== 'active') {
            throw ValidationException::withMessages([
                'record_status' => 'This account is no longer active.'
            ]);
        }

        if ($user->account_status == 'disabled') {
            throw ValidationException::withMessages([
                'account_status' => 'This account is disabled. Please contact administration.'
            ]);
        }
        if (Cache::has('otp_sent_' . $phone_number)) {
            throw new HttpResponseException(
                $this->errorResponse('Please wait before requesting another OTP.', 429)
            );
        }
        return $this->generateOtp($user);

        Cache::put(
            'otp_sent_' . $phone_number,
            true,
            now()->addSeconds(60)
        );
    }

    // تم تغيير المعامل ليقبل كائن User بدلاً من رقم الهاتف لتحسين الأداء
    public function generateOtp(User $user): array
    {
        $phone_number = $user->phone_number;

        if ($phone_number === $this->appleReviewPhone) {
            $otp = $this->appleStaticOtp;
        } else {
            $otp = (string) rand(10000, 99999);
        }

        // تخزين الكود في الـ Cache لمدة 20 دقيقة
        Cache::put('otp_' . $phone_number, $otp, now()->addMinutes(20));

        // إصلاح الخطأ الحرج: استدعاء دالة الإرسال الفعلية
        $isSent = $this->attemptSendOtp($phone_number, $otp);

        if (!$isSent && $phone_number !== $this->appleReviewPhone) {
            // مسح الكود من الكاش إذا فشل الإرسال حتى لا يتعلق النظام
            Cache::forget('otp_' . $phone_number);
            throw new HttpResponseException($this->errorResponse('Failed to send OTP SMS. Please try again later.', 500));
        }

        Log::channel('single')->info('[OTP] OTP generated, SMS sending attempted, and cached for 20 minutes.', [
            'phone_number' => $phone_number,
        ]);

        return [
            'status' => 'otp_generated',
            'phone_number' => $phone_number,
            'otp' => $otp,
            'otp_expires_in' => 20 * 60
        ];
    }

    public function attemptSendOtp(string $phone_number, string $otp): bool
    {
        try {
            if ($phone_number === $this->appleReviewPhone) {
                Log::channel('single')->info('[APPLE REVIEW] attemptSendOtp bypassed (no real SMS).', [
                    'phone_number' => $phone_number,
                ]);
                return true;
            }

            $message = "كود التحقق الخاص بك هو: $otp. يرجى عدم مشاركته مع أي شخص.";

            Log::channel('single')->info('[SMS][OUTBOUND][attemptSendOtp] Preparing to send OTP SMS.', [
                'to' => $phone_number,
                'message' => $message,
            ]);

            $postData = json_encode([
                'to' => $phone_number,
                'message' => $message
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            Log::channel('single')->info('[SMS][OUTBOUND][attemptSendOtp] HTTP Response', [
                'to' => $phone_number,
                'status_code' => $httpCode,
                'body' => $response,
            ]);

            if ($error) {
                Log::channel('single')->error('[SMS][OUTBOUND][attemptSendOtp] cURL Error', [
                    'to' => $phone_number,
                    'error' => $error,
                ]);
                return false;
            }

            return ($httpCode >= 200 && $httpCode < 300);
        } catch (\Exception $e) {
            Log::channel('single')->error('SMS sending error: ' . $e->getMessage(), [
                'to' => $phone_number,
            ]);
            return false;
        }
    }

    public function verifyOtp(string $phone_number, string $otp): array|string
    {
        if ($phone_number === $this->appleReviewPhone && $otp === $this->appleStaticOtp) {
            $user = User::where('phone_number', $phone_number)->first();
            if (!$user) {
                throw new HttpResponseException($this->errorResponse('User not found for Apple review phone number.', 404));
            }

            $token = $user->createToken('auth_token')->plainTextToken;
        } else {
            $cachedOtp = Cache::get('otp_' . $phone_number);

            if (!$cachedOtp || $cachedOtp !== $otp) {
                Log::channel('single')->warning('[OTP] Invalid or expired OTP verification attempt.', [
                    'phone_number' => $phone_number,
                    'code' => $otp,
                ]);
                throw new HttpResponseException($this->errorResponse('OTP is invalid or expired', 422));
            }

            Cache::forget('otp_' . $phone_number);

            $user = User::where('phone_number', $phone_number)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;
        }

        Log::channel('single')->info('[OTP] OTP verified successfully.', [
            'phone_number' => $phone_number,
            'user_id' => $user->id,
        ]);

        return [
            'data' => $user,
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        if (Auth::guard('sanctum')->check()) {
            Auth::guard('sanctum')->user()->currentAccessToken()->delete();
        }
    }
}
