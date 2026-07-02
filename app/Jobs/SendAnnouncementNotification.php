<?php

namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;   
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class SendAnnouncementNotification implements ShouldQueue
{

use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public int $announcementId,
        public string $audience,
        public string $title,
        public string $body

    ) {}

    /**
     * Execute the job.
     */
    public function handle(Messaging $messaging): void
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($this->title, $this->body))
            ->withData(['announcement_id' => (string) $this->announcementId]);

        match ($this->audience) {
            Announcement::AUDIENCE_STUDENT => $messaging->send($message->toTopic('students')),
            Announcement::AUDIENCE_STAFF   => $messaging->send($message->toTopic('staff')),
            Announcement::AUDIENCE_BOTH    => $messaging->send(
                $message->withTarget('condition', "'students' in topics || 'staff' in topics")
            ),
            default => null,
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('فشل إرسال إشعار الإعلان', [
            'announcement_id' => $this->announcementId,
            'error'           => $exception->getMessage(),
        ]);
    }
}
