<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Classroom;
use App\Services\Student\StudentRegisterService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;

class ProcessStudentsImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $batchId
    ) {
    }

    public function handle(StudentRegisterService $studentService): void
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

        $filePath = str_replace('\\', '/', $batch->file_path);

        /*
        | In case an old record contains "public/" in the path,
        | remove it.
        */
        $filePath = preg_replace(
            '#^public/#',
            '',
            $filePath
        );

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        /*
        |--------------------------------------------------------------------------
        | Temporary Local File
        |--------------------------------------------------------------------------
        |
        | FastExcel needs a local file path.
        |
        | This works for both:
        |
        | public/local storage
        | S3/Tigris storage
        |
        */
        $tempPath = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Check Original File
            |--------------------------------------------------------------------------
            */

            if (!Storage::disk($disk)->exists($filePath)) {
                throw new \Exception(
                    "Excel file not found on disk [{$disk}]: {$filePath}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Temporary File
            |--------------------------------------------------------------------------
            */

            $tempPath = tempnam(
                sys_get_temp_dir(),
                'students_import_'
            );

            if ($tempPath === false) {
                throw new \Exception(
                    'Unable to create temporary file for Excel import.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Download File From Storage
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | Do NOT use:
            |
            | Storage::disk($disk)->path()
            |
            | because S3/Tigris is object storage and does not provide
            | a normal local filesystem path.
            |
            */

            $contents = Storage::disk($disk)->get($filePath);

            if ($contents === null || $contents === '') {
                throw new \Exception(
                    "Excel file is empty or could not be read: {$filePath}"
                );
            }

            file_put_contents(
                $tempPath,
                $contents
            );

            Log::info('Students Excel import started', [
                'batch_id' => $batch->id,
                'disk' => $disk,
                'file_path' => $filePath,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Import Excel
            |--------------------------------------------------------------------------
            */

            (new FastExcel)->import(
                $tempPath,
                function ($row) use (
                    $studentService,
                    $batch,
                    &$processedCount,
                    &$successCount,
                    &$failedCount
                ) {

                    $processedCount++;

                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | Academic Data
                        |--------------------------------------------------------------------------
                        */

                        $academicYearName = trim(
                            $row['academic_year_name'] ?? ''
                        );

                        $gradeLevelName = trim(
                            $row['grade_level_name'] ?? ''
                        );

                        $classroomName = trim(
                            $row['class_room_name'] ?? ''
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Academic Year
                        |--------------------------------------------------------------------------
                        */

                        $academicYear = AcademicYear::where(
                            'year_name',
                            $academicYearName
                        )->first();

                        if (!$academicYear) {
                            throw new \Exception(
                                "Academic year '{$academicYearName}' not found."
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Grade Level
                        |--------------------------------------------------------------------------
                        */

                        $grade = GradeLevel::where(
                            'name',
                            $gradeLevelName
                        )->first();

                        if (!$grade) {
                            throw new \Exception(
                                "Grade level '{$gradeLevelName}' not found."
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Classroom
                        |--------------------------------------------------------------------------
                        */

                        $classroomId = null;

                        if ($classroomName !== '') {

                            $classroom = Classroom::where(
                                'academic_year_id',
                                $academicYear->id
                            )
                                ->where(
                                    'grade_level_id',
                                    $grade->id
                                )
                                ->where(
                                    'name',
                                    'like',
                                    "%{$classroomName}%"
                                )
                                ->first();

                            if (!$classroom) {
                                throw new \Exception(
                                    "Classroom '{$classroomName}' not found."
                                );
                            }

                            $classroomId = $classroom->id;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Formatted Student Data
                        |--------------------------------------------------------------------------
                        */

                        $formattedData = [

                            'student' => [

                                'first_name' =>
                                    $row['student_first_name'] ?? '',

                                'last_name' =>
                                    $row['student_last_name'] ?? '',

                                'father_name' =>
                                    $row['student_father_name'] ?? '',

                                'mother_name' =>
                                    $row['student_mother_name'] ?? '',

                                'birth_date' =>
                                    $this->parseDate(
                                        $row['student_birth_date'] ?? null
                                    ),

                                'birth_place' =>
                                    $row['student_birth_place'] ?? '',

                                'address' =>
                                    $row['student_address'] ?? '',

                                'gender' =>
                                    strtolower(
                                        $row['student_gender'] ?? 'male'
                                    ),

                                'nationality' =>
                                    strtolower(
                                        $row['student_nationality'] ?? 'syrian'
                                    ),

                                'phone_number' =>
                                    (string) (
                                        $row['student_phone_number'] ?? ''
                                    ),
                            ],

                            'guardian' => [

                                'first_name' =>
                                    $row['guardian_first_name'] ?? '',

                                'last_name' =>
                                    $row['guardian_last_name'] ?? '',

                                'father_name' =>
                                    $row['guardian_father_name'] ?? '',

                                'mother_name' =>
                                    $row['guardian_mother_name'] ?? '',

                                'birth_date' =>
                                    $this->parseDate(
                                        $row['guardian_birth_date'] ?? null
                                    ),

                                'birth_place' =>
                                    $row['guardian_birth_place'] ?? '',

                                'address' =>
                                    $row['guardian_address'] ?? '',

                                'gender' =>
                                    strtolower(
                                        $row['guardian_gender'] ?? 'male'
                                    ),

                                'nationality' =>
                                    strtolower(
                                        $row['guardian_nationality'] ?? 'syrian'
                                    ),

                                'phone_number' =>
                                    (string) (
                                        $row['guardian_phone_number'] ?? ''
                                    ),
                            ],

                            'enrollment' => [

                                'academic_year_id' =>
                                    $academicYear->id,

                                'grade_level_id' =>
                                    $grade->id,

                                'class_room_id' =>
                                    $classroomId,
                            ],
                        ];

                        /*
                        |--------------------------------------------------------------------------
                        | Register Student
                        |--------------------------------------------------------------------------
                        */

                        $studentService->registerStudentWithGuardian(
                            $formattedData
                        );

                        $successCount++;

                    } catch (\Throwable $e) {

                        /*
                        |--------------------------------------------------------------------------
                        | Record Row Error
                        |--------------------------------------------------------------------------
                        */

                        $failedCount++;

                        ImportError::create([

                            'import_batch_id' =>
                                $batch->id,

                            'row_number' =>
                                $processedCount,

                            'row_data' =>
                                json_encode(
                                    $row,
                                    JSON_UNESCAPED_UNICODE
                                ),

                            'error_message' =>
                                mb_substr(
                                    $e->getMessage(),
                                    0,
                                    250
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

            if (Storage::disk($disk)->exists($filePath)) {

                Storage::disk($disk)->delete(
                    $filePath
                );
            }

            Log::info('Students Excel import completed', [

                'batch_id' =>
                    $batch->id,

                'disk' =>
                    $disk,

                'file_path' =>
                    $filePath,

                'total_rows' =>
                    $processedCount,

                'successful_rows' =>
                    $successCount,

                'failed_rows' =>
                    $failedCount,
            ]);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Job Failed
            |--------------------------------------------------------------------------
            */

            Log::error('Students Import Failed', [

                'batch_id' =>
                    $batch->id,

                'disk' =>
                    $disk,

                'file_path' =>
                    $filePath,

                'error' =>
                    $e->getMessage(),

                'trace' =>
                    $e->getTraceAsString(),
            ]);

            $batch->update([
                'status' => 'failed',
            ]);

            /*
            | Re-throw the exception so Laravel Queue knows
            | that the Job actually failed.
            */
            throw $e;

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Delete Temporary File
            |--------------------------------------------------------------------------
            */

            if (
                $tempPath !== null &&
                file_exists($tempPath)
            ) {
                @unlink($tempPath);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Parse Date
    |--------------------------------------------------------------------------
    */

    private function parseDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        try {

            return Carbon::parse($date)
                ->format('Y-m-d');

        } catch (\Throwable) {

            return null;
        }
    }
}