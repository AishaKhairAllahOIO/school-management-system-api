<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Services\Student\StudentRegisterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Throwable;

class ProcessStudentsImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $batchId) {}

    public function handle(StudentRegisterService $studentService): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        $batch->update(['status' => 'processing']);
        $fullPath = Storage::disk('local')->path($batch->file_path);

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        try {
            (new FastExcel)->import($fullPath, function ($row) use ($studentService, $batch, &$processedCount, &$successCount, &$failedCount) {
                $processedCount++;

                try {
                    // هنا كان يتم إرسال الصف مباشرة للـ Service
                    $studentService->registerStudentWithGuardian($row);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    // هنا كانت عملية تسجيل الخطأ التي تُستخدم لاحقاً في exportErrors
                    ImportError::create([
                        'import_batch_id' => $batch->id,
                        'row_number'      => $processedCount,
                        'row_data'        => json_encode($row),
                        'error_message'   => $e->getMessage(),
                    ]);
                }

                // التحديث الدوري للحالة الذي قرأته ميثود getImportStatus
                if ($processedCount % 10 === 0) {
                    $batch->update([
                        'processed_rows'  => $processedCount,
                        'successful_rows' => $successCount,
                        'failed_rows'     => $failedCount,
                    ]);
                }
            });

            // الحالة النهائية بعد المعالجة
            $batch->update([
                'status'          => 'completed',
                'total_rows'      => $processedCount,
                'processed_rows'  => $processedCount,
                'successful_rows' => $successCount,
                'failed_rows'     => $failedCount,
            ]);

            Storage::disk('local')->delete($batch->file_path);

        } catch (Throwable $e) {
            $batch->update(['status' => 'failed']);
        }
    }
}