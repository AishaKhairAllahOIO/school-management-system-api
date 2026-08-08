<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Scheduling\Actions\GenerateScheduleAction;
use Exception;
use Illuminate\Support\Facades\Log;

class GenerateScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * إعطاء الخوارزمية وقتاً كافياً للتنفيذ (مثلاً 5 دقائق)
     */
    public $timeout = 300;

    public function __construct(
        public int $academicYearId,
        public int $termId
    ) {}

    public function handle(GenerateScheduleAction $action): void
    {
        try {
            Log::info("بدأت عملية توليد الجدول للعام {$this->academicYearId} والفصل {$this->termId}.");

            // استدعاء الخوارزمية
            $schedule = $action->execute($this->academicYearId, $this->termId);

            Log::info("نجح التوليد! تم حفظ الجدول بالرقم: " . $schedule->id);

            // هنا يمكنك إضافة كود لإرسال إشعار Firebase أو بريد إلكتروني للمدير بأن الجدول أصبح جاهزاً

        } catch (Exception $e) {
            Log::error("فشل في توليد الجدول: " . $e->getMessage());
            // رمي الخطأ ليقوم Laravel بتسجيل المهمة كفاشلة (Failed Job)
            throw $e;
        }
    }
}
