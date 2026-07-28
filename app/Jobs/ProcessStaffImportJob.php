<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Services\Staff\StaffRegisterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Rap2hpoutre\FastExcel\FastExcel;

class ProcessStaffImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 🔥 استقبال الـ batchId والدور القادم من الـ URL
    public function __construct(protected int $batchId, protected string $role) {}

    public function handle(StaffRegisterService $staffService): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        $batch->update(['status' => 'processing']);
        $fullPath = Storage::disk('local')->path($batch->file_path);

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        // الأدوار التي تتطلب إلزاميّاً كلمة سر في ملف الإكسل
        $requiresPassword = in_array($this->role, ['secretary', 'adviser']);

        try {
            if (!Storage::disk('local')->exists($batch->file_path)) {
                throw new \Exception("ملف الإكسل غير موجود.");
            }

            (new FastExcel)->import($fullPath, function ($row) use ($staffService, $batch, $requiresPassword, &$processedCount, &$successCount, &$failedCount) {
                $processedCount++;

                try {
                    // 🔥 التحقق من إلزامية كلمة السر لأمين السر والموجه ومدير النظام
                    if ($requiresPassword && empty($row['password'])) {
                        throw new \Exception("كلمة المرور إجبارية في ملف الإكسل للدور الوظيفي: {$this->role}");
                    }

                    $formattedData = [
                        'first_name'       => $row['first_name'] ?? '',
                        'last_name'        => $row['last_name'] ?? '',
                        'father_name'      => $row['father_name'] ?? '',
                        'mother_name'      => $row['mother_name'] ?? '',
                        'birth_date'       => isset($row['birth_date']) ? \Carbon\Carbon::parse($row['birth_date'])->format('Y-m-d') : null,
                        'birth_place'      => $row['birth_place'] ?? '',
                        'address'          => $row['address'] ?? '',
                        'gender'           => strtolower($row['gender'] ?? 'male'),
                        'nationality'      => strtolower($row['nationality'] ?? 'syrian'),
                        'phone_number'     => (string) ($row['phone_number'] ?? ''),
                        'email'            => !empty($row['email']) ? trim($row['email']) : null,
                        
                        // 🔥 فرض الرول القادم حصرياً من الـ URL وتجاهل أي عمود role داخل الملف
                        'role'             => $this->role,
                        'password'         => $row['password'] ?? null,
                    
                        'degree'           => !empty($row['degree']) ? strtolower($row['degree']) : null,
                        'specialization'   => $row['specialization'] ?? null,
                        'university'       => $row['university'] ?? null,
                        'graduation_year'  => !empty($row['graduation_year']) ? (int) $row['graduation_year'] : null,
                        'hire_date'        => isset($row['hire_date']) ? \Carbon\Carbon::parse($row['hire_date'])->format('Y-m-d') : now()->format('Y-m-d'),
                        'experience_years' => (int) ($row['experience_years'] ?? 0),
                        'service_type'     => $row['service_type'] ?? null,
                    ];

                    $staffService->registerSingleStaff($formattedData);
                    $successCount++;
                    
                } catch (\Throwable $e) { 
                    $failedCount++;
                    $friendlyErrorMessage = $this->translateError($e);
                    $safeErrorMessage = mb_substr($friendlyErrorMessage, 0, 250, 'UTF-8');

                    ImportError::create([
                        'import_batch_id' => $batch->id,
                        'row_number'      => $processedCount,
                        'row_data'        => json_encode($row ?? [], JSON_UNESCAPED_UNICODE),
                        'error_message'   => $safeErrorMessage,
                    ]);
                }

                if ($processedCount % 10 === 0) {
                    $batch->update([
                        'processed_rows'  => $processedCount,
                        'successful_rows' => $successCount,
                        'failed_rows'     => $failedCount,
                    ]);
                }
            });

            $batch->update([
                'status'          => 'completed',
                'total_rows'      => $processedCount,
                'processed_rows'  => $processedCount,
                'successful_rows' => $successCount,
                'failed_rows'     => $failedCount,
            ]);

            if (Storage::disk('local')->exists($batch->file_path)) {
                Storage::disk('local')->delete($batch->file_path);
            }

        } catch (\Throwable $e) {
            Log::error("Staff Import Job Failed: " . $e->getMessage());
            $batch->update(['status' => 'failed']);
        }
    }

    private function translateError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'كلمة المرور إجبارية')) {
            return $message;
        }

        if ($e instanceof QueryException && $e->getCode() == 23000) {
            if (str_contains($message, 'users_phone_number_unique')) {
                return 'رقم الهاتف مسجل مسبقاً لموظف آخر، يرجى تغييره.';
            }
            if (str_contains($message, 'users_email_unique')) {
                return 'البريد الإلكتروني مسجل مسبقاً لموظف آخر.';
            }
            return 'يوجد بيانات مكررة أو متعارضة في هذا السطر.';
        }

        if (str_contains($message, 'Failed to parse time string')) {
            return 'تنسيق التاريخ غير صحيح، يرجى كتابته بصيغة صحيحة مثل: YYYY-MM-DD';
        }

        return 'خطأ في البيانات: ' . mb_substr($message, 0, 100, 'UTF-8');
    }
}