<?php

namespace App\Services\Notification;

use App\Models\DeviceToken;
use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MulticastSendReport;

class PushNotificationService
{
    public function __construct(
        private readonly Messaging $messaging
    ) {}

    /**
     * إرسال إشعار لمستخدم واحد (كل أجهزته).
     *
     * @param array $data بيانات إضافية تصل للتطبيق (نوع التنبيه، معرّفه...)
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = $user->deviceTokens()->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * إرسال لعدة مستخدمين دفعة واحدة (مثلاً: طالب + ولي أمره).
     */
    public function sendToUsers(iterable $users, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::whereIn('user_id', collect($users)->pluck('id'))
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * الإرسال الفعلي لـ FCM مع معالجة التوكنات الميتة.
     */
    private function sendToTokens(array $tokens, string $title, string $body, array $data): void
    {
        // كل القيم في data يجب أن تكون نصوصاً (شرط FCM)
        $stringData = array_map(fn ($v) => (string) $v, $data);

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($stringData);

        $report = $this->messaging->sendMulticast($message, $tokens);

        $this->removeInvalidTokens($report);
    }

    /**
     * حذف التوكنات التي رفضها FCM (أجهزة محذوفة، تطبيقات غير مثبّتة).
     * مهم جداً: دونه يمتلئ جدولك بتوكنات ميتة وتبطئ الإرسال.
     */
    private function removeInvalidTokens(MulticastSendReport $report): void
    {
        $invalidTokens = $report->invalidTokens();

        if (! empty($invalidTokens)) {
            DeviceToken::whereIn('fcm_token', $invalidTokens)->delete();
        }
    }
}
