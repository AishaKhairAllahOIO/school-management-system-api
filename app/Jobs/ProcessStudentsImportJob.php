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
            'status' => 'processing'
        ]);


        // كل الملفات من public
        $disk = 'public';


        // تنظيف مسار الملف
        $filePath = str_replace('\\', '/', $batch->file_path);

        // إزالة public/ لو كانت مخزنة بالغلط
        $filePath = preg_replace(
            '/^public\//',
            '',
            $filePath
        );


        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;



        try {


            if (!Storage::disk($disk)->exists($filePath)) {

                throw new \Exception(
                    "Excel file not found: {$filePath}"
                );

            }

            


            $fullPath = Storage::disk($disk)->path($filePath);

            
Log::info('IMPORT FILE DEBUG', [
    'disk' => $disk,
    'file_path' => $batch->file_path,
    'full_path' => $fullPath,
    'exists' => file_exists($fullPath),
    'size' => filesize($fullPath),
]);


            (new FastExcel)->import(
                $fullPath,
                function ($row) use ($studentService, $batch, &$processedCount, &$successCount, &$failedCount) {


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



                        $academicYear = AcademicYear::where(
                            'year_name',
                            $academicYearName
                        )->first();



                        if (!$academicYear) {

                            throw new \Exception(
                                "Academic year '{$academicYearName}' not found."
                            );

                        }



                        $grade = GradeLevel::where(
                            'name',
                            $gradeLevelName
                        )->first();



                        if (!$grade) {

                            throw new \Exception(
                                "Grade level '{$gradeLevelName}' not found."
                            );

                        }



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

                            ]

                        ];




                        $studentService
                            ->registerStudentWithGuardian(
                                $formattedData
                            );


                        $successCount++;



                    } catch (\Throwable $e) {


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




            // حذف ملف الاكسل بعد الانتهاء

            if (Storage::disk($disk)->exists($filePath)) {

                Storage::disk($disk)->delete($filePath);

            }



        } catch (\Throwable $e) {


            Log::error(
                "Students Import Failed: {$e->getMessage()}"
            );



            $batch->update([
                'status' => 'failed'
            ]);

        }

    }



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