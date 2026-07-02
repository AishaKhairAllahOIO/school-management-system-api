<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notification\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // عدد محاولات إعادة التنفيذ عند الفشل (شبكة FCM قد تتعثّر مؤقتاً)
    public int $tries = 3;

    // ثوانٍ الانتظار بين المحاولات
    public int $backoff = 10;

    /**
     * البيانات المُمرَّرة للمهمة.
     * مهم: مرّر معرّفات (IDs) لا كائنات كاملة — أخفّ في التخزين.
     */
    public function __construct(
        public array $userIds,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    /**
     * المنطق الفعلي — يُنفَّذ في الخلفية بواسطة العامل.
     * حقن الخدمة هنا تلقائي (Laravel يحلّها من الحاوية).
     */
    public function handle(PushNotificationService $push): void
    {
        $users = User::whereIn('id', $this->userIds)->get();

        if ($users->isEmpty()) {
            return; // المستخدمون حُذفوا بين الجدولة والتنفيذ
        }

        $push->sendToUsers($users, $this->title, $this->body, $this->data);
    }

    /**
     * يُستدعى لو فشلت كل المحاولات — للتسجيل أو التنبيه.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('فشل إرسال الإشعار نهائياً', [
            'user_ids' => $this->userIds,
            'error'    => $exception->getMessage(),
        ]);
    }
}
