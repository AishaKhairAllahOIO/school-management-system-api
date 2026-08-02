<?php

namespace App\Jobs;

use App\Services\Notification\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public array $userIds,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(PushNotificationService $push): void
    {
        if (empty($this->userIds)) {
            return;
        }

        $push->sendToUserIds($this->userIds, $this->title, $this->body, $this->data);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('فشل إرسال الإشعار اللحظي', [
            'user_ids' => $this->userIds,
            'error'    => $exception->getMessage(),
        ]);
    }
}
