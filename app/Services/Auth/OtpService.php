<?php

namespace App\Services\Auth;

use App\ApiResource;
use App\Models\AcademicYear;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpService
{
    use ApiResource;
    private string $apiKey  = 'cuFkoGNJS3uWuYodSmJnjE:APA91bGkSruRXt0q0HXy_0dr_oL54i35dHRYmU4KU3GDgU_11ZgL51CVsthzZEKbTmXCfI0MRJFg5WrcozL27e2I625PlQeBQZWM_gp1CRy8cKlL87oNPKo';
    private string $apiUrl = 'https://www.traccar.org/sms/';
    private string $appleReviewPhone = '+15555550123';
    private string $appleStaticOtp   = '12345';

    private UserService $user_service;

    public function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
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
                'record_status' => 'This account is no longer active.' // تم تعديل email إلى phone_number للدقة
            ]);
        }

    if ($user->account_status == 'disabled') {
        throw ValidationException::withMessages([
            'account_status' => 'This account is disabled. Please contact administration.'
        ]);
    }


        // التعامل مع رقم مراجعة آبل الاستثنائي
        if ($phone_number === $this->appleReviewPhone) {
            // إذا كان رقم آبل، قم بتوليد أو إرجاع OTP ثابت (مثلاً 1234) دون إرسال SMS فعلي
            // مثال:
            // return $this->generateStaticOtpForApple($phone_number);
        }

        // للمستخدمين العاديين (الطبيعيين)، قم بتوليد وإرسال الـ OTP عبر Traccar SMS Gateway
        return $this->generateOtp($phone_number);
    }
    public function generateOtp(string $phone_number): array
    {

        $user = User::where('phone_number', $phone_number)->first();

        // إذا لم يكن المستخدم مسجلاً من قبل
        if (!$user) {
            Log::channel('single')->warning('[LOGIN] Attempt failed. User not found.', ['phone_number' => $phone_number]);
            throw new HttpResponseException($this->errorResponse('Invalid credentials', 422));
        }
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
                    'code'  => $otp,
                ]);
                throw new HttpResponseException($this->errorResponse('OTP is invalid or expired', 422));
            }

            Cache::forget('otp_' . $phone_number);

            $user = User::where('phone_number', $phone_number)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;
        }

        Log::channel('single')->info('[OTP] OTP verified successfully.', [
            'phone_number' => $phone_number,
            'user_id'      => $user->id,
        ]);

        return [
            'data' => $user,
            'token' => $token,
        ];
    }
    public function refreshToken(User $user): array
    {
        $user->currentAccessToken()->delete();
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $newToken,
        ];
    }
    public function logout(): void
    {

        if (Auth::guard('sanctum')->check()) {
            Auth::guard('sanctum')->user()->currentAccessToken()->delete();
        }
    }
}
