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

    public function __construct(
        protected int $batchId,
        protected string $role
    ) {
    }

    public function handle(StaffRegisterService $staffService): void
    {
        $batch = ImportBatch::find($this->batchId);

        if (!$batch) {
            return;
        }

        $batch->update([
            'status' => 'processing',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Storage Disk
        |--------------------------------------------------------------------------
        |
        | Local:
        | FILESYSTEM_PUBLIC_DISK=public
        |
        | Railway / Tigris:
        | FILESYSTEM_PUBLIC_DISK=s3
        |
        */

        $disk = config('filesystems.public_disk');

        /*
        |--------------------------------------------------------------------------
        | File Path
        |--------------------------------------------------------------------------
        */

        $filePath = str_replace(
            '\\',
            '/',
            $batch->file_path
        );

        /*
        | إزالة public/ إذا كانت موجودة في سجل قديم
        */

        $filePath = preg_replace(
            '#^public/#',
            '',
            $filePath
        );

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        /*
        |--------------------------------------------------------------------------
        | Stream
        |--------------------------------------------------------------------------
        */

        $stream = null;

        /*
        |--------------------------------------------------------------------------
        | Password Requirement
        |--------------------------------------------------------------------------
        */

        $requiresPassword = in_array(
            $this->role,
            [
                'secretary',
                'adviser',
                'super_admin',
            ],
            true
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | Check File
            |--------------------------------------------------------------------------
            */

            if (!Storage::disk($disk)->exists($filePath)) {
                throw new \Exception(
                    "Excel file not found on disk [{$disk}]: {$filePath}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Read Excel File As Stream
            |--------------------------------------------------------------------------
            |
            | يعمل مع:
            |
            | - local
            | - s3
            | - Tigris
            |
            | ولا يعتمد على Storage::path()
            |
            */

            $stream = Storage::disk($disk)
                ->readStream($filePath);

            if (!$stream) {
                throw new \Exception(
                    "Unable to read excel file from disk [{$disk}]: {$filePath}"
                );
            }

            Log::info(
                'Staff Excel import started',
                [
                    'batch_id' => $batch->id,
                    'disk' => $disk,
                    'file_path' => $filePath,
                    'role' => $this->role,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Import Excel
            |--------------------------------------------------------------------------
            */

            (new FastExcel)->import(
                $stream,
                function ($row) use (
                    $staffService,
                    $batch,
                    $requiresPassword,
                    &$processedCount,
                    &$successCount,
                    &$failedCount
                ) {

                    $processedCount++;

                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | Password Validation
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $requiresPassword &&
                            empty($row['password'])
                        ) {
                            throw new \Exception(
                                "The password is required in the Excel file for the role: {$this->role}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Format Staff Data
                        |--------------------------------------------------------------------------
                        */

                        $formattedData = [

                            'first_name' =>
                                $row['first_name'] ?? '',

                            'last_name' =>
                                $row['last_name'] ?? '',

                            'father_name' =>
                                $row['father_name'] ?? '',

                            'mother_name' =>
                                $row['mother_name'] ?? '',

                            'birth_date' =>
                                isset($row['birth_date'])
                                    ? \Carbon\Carbon::parse(
                                        $row['birth_date']
                                    )->format('Y-m-d')
                                    : null,

                            'birth_place' =>
                                $row['birth_place'] ?? '',

                            'address' =>
                                $row['address'] ?? '',

                            'gender' =>
                                strtolower(
                                    $row['gender'] ?? 'male'
                                ),

                            'nationality' =>
                                strtolower(
                                    $row['nationality'] ?? 'syrian'
                                ),

                            'phone_number' =>
                                (string) (
                                    $row['phone_number'] ?? ''
                                ),

                            'email' =>
                                !empty($row['email'])
                                    ? trim($row['email'])
                                    : null,

                            /*
                            | الدور يأتي من الـ API / Job
                            | وليس من Excel
                            */

                            'role' =>
                                $this->role,

                            'password' =>
                                $row['password'] ?? null,

                            'degree' =>
                                !empty($row['degree'])
                                    ? strtolower($row['degree'])
                                    : null,

                            'specialization' =>
                                $row['specialization'] ?? null,

                            'university' =>
                                $row['university'] ?? null,

                            'graduation_year' =>
                                !empty($row['graduation_year'])
                                    ? (int) $row['graduation_year']
                                    : null,

                            'hire_date' =>
                                isset($row['hire_date'])
                                    ? \Carbon\Carbon::parse(
                                        $row['hire_date']
                                    )->format('Y-m-d')
                                    : now()->format('Y-m-d'),

                            'experience_years' =>
                                (int) (
                                    $row['experience_years'] ?? 0
                                ),

                            'service_type' =>
                                $row['service_type'] ?? null,
                        ];

                        /*
                        |--------------------------------------------------------------------------
                        | Register Staff
                        |--------------------------------------------------------------------------
                        */

                        $staffService->registerSingleStaff(
                            $formattedData
                        );

                        $successCount++;

                    } catch (\Throwable $e) {

                        /*
                        |--------------------------------------------------------------------------
                        | Row Failed
                        |--------------------------------------------------------------------------
                        */

                        $failedCount++;

                        $errorMessage = $this->translateError($e);

                        ImportError::create([

                            'import_batch_id' =>
                                $batch->id,

                            'row_number' =>
                                $processedCount,

                            'row_data' =>
                                json_encode(
                                    $row ?? [],
                                    JSON_UNESCAPED_UNICODE
                                ),

                            'error_message' =>
                                mb_substr(
                                    $errorMessage,
                                    0,
                                    250,
                                    'UTF-8'
                                ),
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update Progress Every 10 Rows
                    |--------------------------------------------------------------------------
                    */

                    if ($processedCount % 10 === 0) {

                        $batch->update([

                            'processed_rows' =>
                                $processedCount,

                            'successful_rows' =>
                                $successCount,

                            'failed_rows' =>
                                $failedCount,
                        ]);
                    }
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Import Completed
            |--------------------------------------------------------------------------
            */

            $batch->update([

                'status' =>
                    'completed',

                'total_rows' =>
                    $processedCount,

                'processed_rows' =>
                    $processedCount,

                'successful_rows' =>
                    $successCount,

                'failed_rows' =>
                    $failedCount,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Delete Original Excel File
            |--------------------------------------------------------------------------
            */

            if (
                Storage::disk($disk)
                    ->exists($filePath)
            ) {

                Storage::disk($disk)
                    ->delete($filePath);
            }

            /*
            |--------------------------------------------------------------------------
            | Completed Log
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Staff Excel import completed',
                [
                    'batch_id' =>
                        $batch->id,

                    'disk' =>
                        $disk,

                    'file_path' =>
                        $filePath,

                    'role' =>
                        $this->role,

                    'total_rows' =>
                        $processedCount,

                    'successful_rows' =>
                        $successCount,

                    'failed_rows' =>
                        $failedCount,
                ]
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Job Failed
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Staff Import Job Failed',
                [
                    'batch_id' =>
                        $batch->id,

                    'disk' =>
                        $disk,

                    'file_path' =>
                        $filePath,

                    'role' =>
                        $this->role,

                    'error' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            $batch->update([
                'status' => 'failed',
            ]);

            /*
            | مهم:
            | إعادة رمي الخطأ تجعل Laravel Queue يعتبر الـ Job فاشلًا
            */

            throw $e;

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Close Storage Stream
            |--------------------------------------------------------------------------
            */

            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Translate Error
    |--------------------------------------------------------------------------
    */

    private function translateError(\Throwable $e): string
    {
        $message = $e->getMessage();

        /*
        |--------------------------------------------------------------------------
        | Missing Password
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $message,
                'The password is required in the Excel file'
            )
        ) {
            return $message;
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Database Data
        |--------------------------------------------------------------------------
        */

        if (
            $e instanceof QueryException &&
            $e->getCode() == 23000
        ) {

            if (
                str_contains(
                    $message,
                    'users_phone_number_unique'
                )
            ) {
                return 'The phone number is already taken by another staff member.';
            }

            if (
                str_contains(
                    $message,
                    'users_email_unique'
                )
            ) {
                return 'The email address is already taken by another staff member.';
            }

            return 'Duplicate or conflicting data exists in this row.';
        }

        /*
        |--------------------------------------------------------------------------
        | Invalid Date
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $message,
                'Failed to parse time string'
            )
        ) {
            return 'Invalid date format, please use YYYY-MM-DD.';
        }

        /*
        |--------------------------------------------------------------------------
        | Generic Error
        |--------------------------------------------------------------------------
        */

        return 'Data error: ' .
            mb_substr(
                $message,
                0,
                100,
                'UTF-8'
            );
    }
}