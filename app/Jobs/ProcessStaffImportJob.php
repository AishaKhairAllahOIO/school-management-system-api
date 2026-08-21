<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Services\Staff\StaffRegisterService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Rap2hpoutre\FastExcel\FastExcel;

class ProcessStaffImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Queue configuration
     */
    public int $tries = 1;

    public int $timeout = 1200;

    /**
     * Import batch ID
     */
    protected int $batchId;

    /**
     * Staff role
     */
    protected string $role;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $batchId,
        string $role
    ) {
        $this->batchId = $batchId;
        $this->role = $role;
    }

    /**
     * Execute the job.
     */
    public function handle(
        StaffRegisterService $staffService
    ): void {
        $batch = ImportBatch::find($this->batchId);

        if (!$batch) {
            Log::warning(
                'Staff import batch not found',
                [
                    'batch_id' => $this->batchId,
                    'role' => $this->role,
                ]
            );

            return;
        }

        /**
         * ---------------------------------------------------------
         * Counters
         * ---------------------------------------------------------
         */
        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        /**
         * ---------------------------------------------------------
         * Temporary file
         * ---------------------------------------------------------
         */
        $tempPath = null;

        /**
         * ---------------------------------------------------------
         * Storage disk
         * ---------------------------------------------------------
         */
        $disk = config('filesystems.public_disk');

        if (!$disk) {
            $disk = config(
                'filesystems.default',
                'local'
            );
        }

        /**
         * ---------------------------------------------------------
         * File path
         * ---------------------------------------------------------
         */
        $filePath = str_replace(
            '\\',
            '/',
            $batch->file_path
        );

        /**
         * Remove public/ if old records contain it.
         */
        $filePath = preg_replace(
            '#^public/#',
            '',
            $filePath
        );

        /**
         * ---------------------------------------------------------
         * Password requirement
         * ---------------------------------------------------------
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

        /**
         * ---------------------------------------------------------
         * Start processing
         * ---------------------------------------------------------
         */
        $batch->update([
            'status' => 'processing',
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
        ]);

        Log::info(
            'STAFF IMPORT JOB STARTED',
            [
                'batch_id' => $batch->id,
                'role' => $this->role,
                'disk' => $disk,
                'file_path' => $filePath,
                'requires_password' => $requiresPassword,
            ]
        );

        try {

            /**
             * -----------------------------------------------------
             * Check original file
             * -----------------------------------------------------
             */
            if (!Storage::disk($disk)->exists($filePath)) {
                throw new \Exception(
                    "Excel file not found on disk [{$disk}]: {$filePath}"
                );
            }

            /**
             * -----------------------------------------------------
             * Get file size
             * -----------------------------------------------------
             */
            try {
                $fileSize = Storage::disk($disk)
                    ->size($filePath);
            } catch (\Throwable) {
                $fileSize = null;
            }

            Log::info(
                'STAFF IMPORT FILE FOUND',
                [
                    'batch_id' => $batch->id,
                    'disk' => $disk,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                ]
            );

            /**
             * -----------------------------------------------------
             * Create temporary local file
             * -----------------------------------------------------
             *
             * Same approach as the working students import.
             */
            $tempPath = tempnam(
                sys_get_temp_dir(),
                'staff_import_'
            );

            if ($tempPath === false) {
                throw new \Exception(
                    'Unable to create temporary file for staff Excel import.'
                );
            }

            Log::info(
                'STAFF IMPORT TEMP FILE CREATED',
                [
                    'batch_id' => $batch->id,
                    'temp_path' => $tempPath,
                ]
            );

            /**
             * -----------------------------------------------------
             * Read file from storage
             * -----------------------------------------------------
             */
            $stream = Storage::disk($disk)
                ->readStream($filePath);

            if ($stream === false) {
                throw new \Exception(
                    "Excel file could not be read: {$filePath}"
                );
            }

            /**
             * -----------------------------------------------------
             * Open temporary file
             * -----------------------------------------------------
             */
            $output = fopen(
                $tempPath,
                'wb'
            );

            if ($output === false) {

                if (is_resource($stream)) {
                    fclose($stream);
                }

                throw new \Exception(
                    "Unable to open temporary file for writing: {$tempPath}"
                );
            }

            /**
             * -----------------------------------------------------
             * Copy storage stream to local temp file
             * -----------------------------------------------------
             */
            try {

                $bytesCopied = stream_copy_to_stream(
                    $stream,
                    $output
                );

            } finally {

                fclose($output);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            /**
             * -----------------------------------------------------
             * Verify copied file
             * -----------------------------------------------------
             */
            if (
                $bytesCopied === false ||
                $bytesCopied === 0
            ) {
                throw new \Exception(
                    "Excel file is empty or could not be copied: {$filePath}"
                );
            }

            Log::info(
                'STAFF IMPORT FILE COPIED TO TEMP',
                [
                    'batch_id' => $batch->id,
                    'bytes_copied' => $bytesCopied,
                    'temp_path' => $tempPath,
                ]
            );

            /**
             * -----------------------------------------------------
             * Verify temp file exists
             * -----------------------------------------------------
             */
            if (!file_exists($tempPath)) {
                throw new \Exception(
                    "Temporary Excel file does not exist: {$tempPath}"
                );
            }

            /**
             * -----------------------------------------------------
             * Verify temp file size
             * -----------------------------------------------------
             */
            $tempFileSize = filesize($tempPath);

            if (
                $tempFileSize === false ||
                $tempFileSize === 0
            ) {
                throw new \Exception(
                    'Temporary Excel file is empty.'
                );
            }

            Log::info(
                'STAFF IMPORT FASTEXCEL STARTING',
                [
                    'batch_id' => $batch->id,
                    'temp_path' => $tempPath,
                    'temp_file_size' => $tempFileSize,
                ]
            );

            /**
             * -----------------------------------------------------
             * Import Excel
             * -----------------------------------------------------
             *
             * IMPORTANT:
             * FastExcel receives a LOCAL FILE PATH,
             * exactly like the working students import.
             */
            (new FastExcel)->import(
                $tempPath,
                function ($row) use (
                    $staffService,
                    $batch,
                    $requiresPassword,
                    &$processedCount,
                    &$successCount,
                    &$failedCount
                ) {

                    /**
                     * -------------------------------------------------
                     * Count row
                     * -------------------------------------------------
                     */
                    $processedCount++;

                    /**
                     * Current row number
                     */
                    $rowNumber = $processedCount;

                    Log::debug(
                        'STAFF IMPORT ROW STARTED',
                        [
                            'batch_id' => $batch->id,
                            'row_number' => $rowNumber,
                            'columns' => is_array($row)
                                ? array_keys($row)
                                : [],
                        ]
                    );

                    try {

                        /**
                         * -------------------------------------------------
                         * Validate row
                         * -------------------------------------------------
                         */
                        if (!is_array($row)) {
                            throw new \Exception(
                                'Invalid Excel row format.'
                            );
                        }

                        /**
                         * -------------------------------------------------
                         * Password validation
                         * -------------------------------------------------
                         */
                        if (
                            $requiresPassword &&
                            empty(
                                trim(
                                    (string) (
                                        $row['password'] ?? ''
                                    )
                                )
                            )
                        ) {
                            throw new \Exception(
                                "The password is required in the Excel file for the role: {$this->role}"
                            );
                        }

                        /**
                         * -------------------------------------------------
                         * Parse dates
                         * -------------------------------------------------
                         */
                        $birthDate = $this->parseDate(
                            $row['birth_date'] ?? null
                        );

                        $hireDate = $this->parseDate(
                            $row['hire_date'] ?? null
                        );

                        /**
                         * -------------------------------------------------
                         * Format staff data
                         * -------------------------------------------------
                         */
                        $formattedData = [

                            'first_name' =>
                                trim(
                                    (string) (
                                        $row['first_name'] ?? ''
                                    )
                                ),

                            'last_name' =>
                                trim(
                                    (string) (
                                        $row['last_name'] ?? ''
                                    )
                                ),

                            'father_name' =>
                                trim(
                                    (string) (
                                        $row['father_name'] ?? ''
                                    )
                                ),

                            'mother_name' =>
                                trim(
                                    (string) (
                                        $row['mother_name'] ?? ''
                                    )
                                ),

                            'birth_date' =>
                                $birthDate,

                            'birth_place' =>
                                trim(
                                    (string) (
                                        $row['birth_place'] ?? ''
                                    )
                                ),

                            'address' =>
                                trim(
                                    (string) (
                                        $row['address'] ?? ''
                                    )
                                ),

                            'gender' =>
                                strtolower(
                                    trim(
                                        (string) (
                                            $row['gender'] ?? 'male'
                                        )
                                    )
                                ),

                            'nationality' =>
                                strtolower(
                                    trim(
                                        (string) (
                                            $row['nationality'] ?? 'syrian'
                                        )
                                    )
                                ),

                            'phone_number' =>
                                trim(
                                    (string) (
                                        $row['phone_number'] ?? ''
                                    )
                                ),

                            'email' =>
                                !empty($row['email'])
                                    ? trim(
                                        (string) $row['email']
                                    )
                                    : null,

                            /**
                             * Role comes from Job/API.
                             */
                            'role' =>
                                $this->role,

                            'password' =>
                                !empty($row['password'])
                                    ? (string) $row['password']
                                    : null,

                            'degree' =>
                                !empty($row['degree'])
                                    ? strtolower(
                                        trim(
                                            (string) $row['degree']
                                        )
                                    )
                                    : null,

                            'specialization' =>
                                !empty($row['specialization'])
                                    ? trim(
                                        (string) $row['specialization']
                                    )
                                    : null,

                            'university' =>
                                !empty($row['university'])
                                    ? trim(
                                        (string) $row['university']
                                    )
                                    : null,

                            'graduation_year' =>
                                !empty($row['graduation_year'])
                                    ? (int) $row['graduation_year']
                                    : null,

                            'hire_date' =>
                                $hireDate
                                    ?? now()->format('Y-m-d'),

                            'experience_years' =>
                                !empty($row['experience_years'])
                                    ? (int) $row['experience_years']
                                    : 0,

                            'service_type' =>
                                !empty($row['service_type'])
                                    ? trim(
                                        (string) $row['service_type']
                                    )
                                    : null,
                        ];

                        /**
                         * -------------------------------------------------
                         * Basic logging before service
                         * -------------------------------------------------
                         */
                        Log::debug(
                            'STAFF IMPORT REGISTERING ROW',
                            [
                                'batch_id' => $batch->id,
                                'row_number' => $rowNumber,
                                'role' => $this->role,
                                'first_name' =>
                                    $formattedData['first_name'],
                                'last_name' =>
                                    $formattedData['last_name'],
                                'phone_number' =>
                                    $formattedData['phone_number'],
                                'email' =>
                                    $formattedData['email'],
                            ]
                        );

                        /**
                         * -------------------------------------------------
                         * Register staff
                         * -------------------------------------------------
                         */
                        $staffService->registerSingleStaff(
                            $formattedData
                        );

                        /**
                         * -------------------------------------------------
                         * Success
                         * -------------------------------------------------
                         */
                        $successCount++;

                        Log::debug(
                            'STAFF IMPORT ROW SUCCESS',
                            [
                                'batch_id' => $batch->id,
                                'row_number' => $rowNumber,
                                'success_count' => $successCount,
                            ]
                        );

                    } catch (\Throwable $e) {

                        /**
                         * -------------------------------------------------
                         * Row failed
                         * -------------------------------------------------
                         */
                        $failedCount++;

                        $errorMessage =
                            $this->translateError($e);

                        /**
                         * Save row error
                         */
                        ImportError::create([
                            'import_batch_id' =>
                                $batch->id,

                            'row_number' =>
                                $rowNumber,

                            'row_data' =>
                                json_encode(
                                    $row,
                                    JSON_UNESCAPED_UNICODE |
                                    JSON_UNESCAPED_SLASHES
                                ),

                            'error_message' =>
                                mb_substr(
                                    $errorMessage,
                                    0,
                                    250,
                                    'UTF-8'
                                ),
                        ]);

                        /**
                         * Detailed log.
                         */
                        Log::error(
                            'STAFF IMPORT ROW FAILED',
                            [
                                'batch_id' => $batch->id,
                                'row_number' => $rowNumber,
                                'role' => $this->role,
                                'error' => $e->getMessage(),
                                'exception' =>
                                    get_class($e),
                                'file' =>
                                    $e->getFile(),
                                'line' =>
                                    $e->getLine(),
                                'trace' =>
                                    $e->getTraceAsString(),
                            ]
                        );
                    }

                    /**
                     * -------------------------------------------------
                     * Update progress
                     * -------------------------------------------------
                     *
                     * Update every row.
                     * This makes the frontend always receive
                     * the current state.
                     */
                    $batch->update([
                        'processed_rows' =>
                            $processedCount,

                        'successful_rows' =>
                            $successCount,

                        'failed_rows' =>
                            $failedCount,
                    ]);

                }
            );

            /**
             * ---------------------------------------------------------
             * Final batch update
             * ---------------------------------------------------------
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

            /**
             * ---------------------------------------------------------
             * Log completion
             * ---------------------------------------------------------
             */
            Log::info(
                'STAFF IMPORT JOB COMPLETED',
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

            /**
             * ---------------------------------------------------------
             * Delete original file
             * ---------------------------------------------------------
             */
            if (
                Storage::disk($disk)
                    ->exists($filePath)
            ) {

                Storage::disk($disk)
                    ->delete($filePath);

                Log::info(
                    'STAFF IMPORT ORIGINAL FILE DELETED',
                    [
                        'batch_id' =>
                            $batch->id,

                        'file_path' =>
                            $filePath,
                    ]
                );
            }

        } catch (\Throwable $e) {

            /**
             * ---------------------------------------------------------
             * Job failed
             * ---------------------------------------------------------
             */
            Log::error(
                'STAFF IMPORT JOB FAILED',
                [
                    'batch_id' =>
                        $batch->id,

                    'disk' =>
                        $disk,

                    'file_path' =>
                        $filePath,

                    'role' =>
                        $this->role,

                    'processed_rows' =>
                        $processedCount,

                    'successful_rows' =>
                        $successCount,

                    'failed_rows' =>
                        $failedCount,

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            /**
             * Keep the actual progress in DB.
             */
            $batch->update([
                'status' =>
                    'failed',

                'total_rows' =>
                    $processedCount,

                'processed_rows' =>
                    $processedCount,

                'successful_rows' =>
                    $successCount,

                'failed_rows' =>
                    $failedCount,
            ]);

            /**
             * Re-throw so Laravel Queue knows
             * that the job failed.
             */
            throw $e;

        } finally {

            /**
             * ---------------------------------------------------------
             * Delete temporary file
             * ---------------------------------------------------------
             */
            if (
                $tempPath !== null &&
                file_exists($tempPath)
            ) {
                @unlink($tempPath);

                Log::debug(
                    'STAFF IMPORT TEMP FILE DELETED',
                    [
                        'batch_id' =>
                            $batch->id,

                        'temp_path' =>
                            $tempPath,
                    ]
                );
            }
        }
    }

    /**
     * -------------------------------------------------------------
     * Parse date
     * -------------------------------------------------------------
     *
     * Same approach used by the working student import.
     *
     * No PhpSpreadsheet excelToDateTimeObject().
     */
    private function parseDate($date): ?string
    {
        if (
            $date === null ||
            $date === ''
        ) {
            return null;
        }

        try {

            return Carbon::parse($date)
                ->format('Y-m-d');

        } catch (\Throwable $e) {

            Log::warning(
                'STAFF IMPORT DATE PARSE FAILED',
                [
                    'date' =>
                        $date,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /**
     * -------------------------------------------------------------
     * Translate error
     * -------------------------------------------------------------
     */
    private function translateError(
        \Throwable $e
    ): string {

        $message = $e->getMessage();

        /**
         * Missing password
         */
        if (
            str_contains(
                $message,
                'The password is required in the Excel file'
            )
        ) {
            return $message;
        }

        /**
         * Database duplicate / conflict
         */
        if (
            $e instanceof QueryException &&
            (string) $e->getCode() === '23000'
        ) {

            if (
                str_contains(
                    $message,
                    'users_phone_number_unique'
                )
            ) {
                return
                    'The phone number is already taken by another staff member.';
            }

            if (
                str_contains(
                    $message,
                    'users_email_unique'
                )
            ) {
                return
                    'The email address is already taken by another staff member.';
            }

            return
                'Duplicate or conflicting data exists in this row.';
        }

        /**
         * Invalid date
         */
        if (
            str_contains(
                $message,
                'Failed to parse time string'
            ) ||
            str_contains(
                strtolower($message),
                'invalid date'
            )
        ) {
            return
                'Invalid date format, please use YYYY-MM-DD.';
        }

        /**
         * Generic error
         */
        return
            'Data error: ' .
            mb_substr(
                $message,
                0,
                100,
                'UTF-8'
            );
    }

    /**
     * -------------------------------------------------------------
     * Laravel queue failed callback
     * -------------------------------------------------------------
     */
    public function failed(
        ?\Throwable $exception
    ): void {

        $batch = ImportBatch::find(
            $this->batchId
        );

        if (!$batch) {
            return;
        }

        Log::error(
            'STAFF IMPORT QUEUE FAILED CALLBACK',
            [
                'batch_id' =>
                    $this->batchId,

                'role' =>
                    $this->role,

                'error' =>
                    $exception?->getMessage(),

                'exception' =>
                    $exception
                        ? get_class($exception)
                        : null,
            ]
        );

        $batch->update([
            'status' => 'failed',
        ]);
    }
}