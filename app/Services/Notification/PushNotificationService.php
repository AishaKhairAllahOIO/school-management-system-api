<?php

namespace App\Services\Notification;

use App\Models\DeviceToken;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MulticastSendReport;

class PushNotificationService
{
    public function __construct(
        private readonly Messaging $messaging
    ) {}

    public function sendToUserIds(array $userIds, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::whereIn('user_id', $userIds)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    private function sendToTokens(array $tokens, string $title, string $body, array $data): void
    {
        $stringData = array_map(fn ($v) => (string) $v, $data);

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($stringData);

        $report = $this->messaging->sendMulticast($message, $tokens);

        $this->removeInvalidTokens($report);
    }

    private function removeInvalidTokens(MulticastSendReport $report): void
    {
        $invalidTokens = $report->invalidTokens();

        if (! empty($invalidTokens)) {
            DeviceToken::whereIn('fcm_token', $invalidTokens)->delete();
        }
    }
}
