<?php

namespace App\Services\Auth;

use App\ApiResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    use ApiResource;
    private string $apiKey  = '1f8d84c2-2f74-4fc0-93b1-d0fdc94b5284';
    private string $apiUrl  = 'http://192.168.1.104:8082/';
    private string $appleReviewPhone = '+15555550123';
    private string $appleStaticOtp   = '12345';

    public function login(string $phone_number, string $password): array|string
    {
        // 1. البحث عن المستخدم برقم الهاتف
        $user = User::where('phone_number', $phone_number)->first();

        // إذا لم يكن المستخدم مسجلاً من قبل
        if (!$user) {
            Log::channel('single')->warning('[LOGIN] Attempt failed. User not found.', ['phone_number' => $phone_number]);
            throw new HttpResponseException($this->errorResponse('Invalid credentials', 400));
        }

        // 2. التحقق من صحة كلمة المرور (باستثناء حساب مراجعة أبل)
        if ($phone_number !== $this->appleReviewPhone && !Hash::check($password, $user->password)) {
            Log::channel('single')->warning('[LOGIN] Attempt failed. Wrong password.', ['phone_number' => $phone_number]);
            throw new HttpResponseException($this->errorResponse('Invalid credentials', 400));
        }

        return $this->generateOtp($phone_number);
    }
    public function generateOtp(string $phone_number): array
    {
        // 3. توليد الكود (ثابت لأبل، وعشوائي للبقية)
        if ($phone_number === $this->appleReviewPhone) {
            $otp = $this->appleStaticOtp;
        } else {
            $otp = (string) rand(10000, 99999);
        }

        // 4. تخزين الكود في الـ Cache لمدة 10 دقائق
        // نستخدم مفتاح فريد يحتوي على رقم الهاتف لضمان عدم التداخل
        Cache::put('otp_' . $phone_number, $otp, now()->addMinutes(20));

        Log::channel('single')->info('[OTP] OTP generated and cached for 20 minutes.', [
            'phone_number' => $phone_number,
            'otp'   => $otp,
        ]);

        return [
            'status' => 'otp_generated',
            'phone_number'  => $phone_number,
            'otp'    => $otp,
            'otp_expires_in' => 20 * 60
        ];
    }
    public function attemptSendOtp(string $phone_number, string $otp): bool
    {
        try {
            // تخطي الإرسال الحقيقي لمراجعي أبل
            if ($phone_number === $this->appleReviewPhone) {
                Log::channel('single')->info('[APPLE REVIEW] attemptSendOtp bypassed (no real SMS).', [
                    'phone_number' => $phone_number,
                    'otp'   => $otp,
                ]);
                return true;
            }

            $message = "كود التحقق الخاص بك هو: $otp. يرجى عدم مشاركته مع أي شخص.";

            Log::channel('single')->info('[SMS][OUTBOUND][attemptSendOtp] Preparing to send OTP SMS.', [
                'to'      => $phone_number,
                'message' => $message,
            ]);

            $postData = json_encode([
                'to'      => $phone_number,
                'message' => $message
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: ' . $this->apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);

            Log::channel('single')->info('[SMS][OUTBOUND][attemptSendOtp] HTTP Response', [
                'to'          => $phone_number,
                'status_code' => $httpCode,
                'body'        => $response,
            ]);

            if ($error) {
                Log::channel('single')->error('[SMS][OUTBOUND][attemptSendOtp] cURL Error', [
                    'to'    => $phone_number,
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
    public function verifyOtp(string $phone_number, string $code): array|string
    {
        // 1. معالجة خاصة لمراجعي أبل (بدون كاش)
        if ($phone_number === $this->appleReviewPhone && $code === $this->appleStaticOtp) {
            $user = User::where('phone_number', $phone_number)->first();
            if (!$user) {
                throw new HttpResponseException($this->errorResponse('User not found for Apple review phone number.', 404));
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return [
                'token' => $token,
                'user'  => $user,
            ];
        }

        // 2. جلب الكود من الـ Cache
        $cachedOtp = Cache::get('otp_' . $phone_number);

        // 3. مطابقة الكود
        if (!$cachedOtp || $cachedOtp !== $code) {
            Log::channel('single')->warning('[OTP] Invalid or expired OTP verification attempt.', [
                'phone_number' => $phone_number,
                'code'  => $code,
            ]);
            throw new HttpResponseException($this->errorResponse('OTP is invalid or expired', 400));
        }

        // 4. حذف الكود من الكاش فوراً بعد استخدامه بنجاح لمرة واحدة فقط
        Cache::forget('otp_' . $phone_number);

        // 5. جلب المستخدم وتوليد التوكن (Sanctum)
        $user = User::where('phone_number', $phone_number)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::channel('single')->info('[OTP] OTP verified from Cache successfully.', [
            'phone_number' => $phone_number,
            'user_id'      => $user->id,
        ]);

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }
    public function logout($user): void
    {

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
    }

}
