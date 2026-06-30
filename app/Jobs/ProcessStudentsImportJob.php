<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\ClassRoom;
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

        try {
            $firstRow = (new FastExcel)->import($fullPath)->first();
            
            $mandatoryHeaders = [
                'student_phone_number', 'student_first_name', 'student_last_name', 'student_father_name', 
                'student_mother_name', 'student_birth_date', 'student_birth_place', 'student_address', 
                'student_gender', 'student_nationality',
                'guardian_phone_number', 'guardian_first_name', 'guardian_last_name', 'guardian_father_name', 
                'guardian_mother_name', 'guardian_birth_date', 'guardian_birth_place', 'guardian_address', 
                'guardian_gender', 'guardian_nationality',
                'academic_year_name', 'grade_level_name', 'class_room_name'
            ];

            foreach ($mandatoryHeaders as $header) {
                if (!array_key_exists($header, $firstRow)) {
                    throw new \Exception("The mandatory Excel column [{$header}] is missing.");
                }
            }
        } catch (Throwable $headerError) {
            $batch->update(['status' => 'failed', 'failed_rows' => 0, 'processed_rows' => 0]);
            ImportError::create([
                'import_batch_id' => $batch->id,
                'row_number'      => 1,
                'row_data'        => $firstRow ?? [],
                'error_message'   => $headerError->getMessage(),
            ]);
            Storage::disk('local')->delete($batch->file_path);
            return;
        }

        $yearsMap  = AcademicYear::pluck('id', 'year_name')->toArray(); 
        $gradesMap = GradeLevel::pluck('id', 'grade_name')->toArray();   
        $roomsMap  = ClassRoom::pluck('id', 'name')->toArray();    

        $processedCount = 0;
        $successCount   = 0;
        $failedCount    = 0;
        $rowNumber      = 1; 

        try {
            (new FastExcel)->import($fullPath, function ($row) use (
                $studentService, $batch, $yearsMap, $gradesMap, $roomsMap,
                &$processedCount, &$successCount, &$failedCount, &$rowNumber
            ) {
                $rowNumber++; 
                $processedCount++;

                try {
                    $yearName  = trim((string)($row['academic_year_name'] ?? ''));
                    $gradeName = trim((string)($row['grade_level_name'] ?? ''));
                    $roomName  = trim((string)($row['class_room_name'] ?? ''));

                    $yearId  = $yearsMap[$yearName]   ?? throw new \Exception("Academic year [{$yearName}] is not defined in the system.");
                    $gradeId = $gradesMap[$gradeName] ?? throw new \Exception("Grade level [{$gradeName}] does not exist in the database.");
                    $roomId  = $roomsMap[$roomName]   ?? throw new \Exception("Classroom [{$roomName}] is not registered in this school.");

                    if (empty(trim($row['student_first_name'])) || empty(trim($row['student_phone_number']))) {
                        throw new \Exception("Student core data (first_name or phone_number) cannot be empty.");
                    }

                    $parseDate = fn($val) => $val instanceof \DateTimeInterface ? $val->format('Y-m-d') : trim((string)$val);

                    $payload = [
                        'guardian' => [
                            'phone_number' => trim((string)$row['guardian_phone_number']),
                            'first_name'   => trim((string)$row['guardian_first_name']),
                            'last_name'    => trim((string)$row['guardian_last_name']),
                            'father_name'  => trim((string)$row['guardian_father_name']),
                            'mother_name'  => trim((string)$row['guardian_mother_name']),
                            'birth_date'   => $parseDate($row['guardian_birth_date']),
                            'birth_place'  => trim((string)$row['guardian_birth_place']),
                            'address'      => trim((string)$row['guardian_address']),
                            'gender'       => trim(strtolower((string)$row['guardian_gender'])),
                            'nationality'  => trim(strtolower((string)($row['guardian_nationality'] ?? 'syrian'))),
                        ],
                        'student' => [
                            'phone_number' => trim((string)$row['student_phone_number']),
                            'first_name'   => trim((string)$row['student_first_name']),
                            'last_name'    => trim((string)$row['student_last_name']),
                            'father_name'  => trim((string)$row['student_father_name']),
                            'mother_name'  => trim((string)$row['student_mother_name']),
                            'birth_date'   => $parseDate($row['student_birth_date']),
                            'birth_place'  => trim((string)$row['student_birth_place']),
                            'address'      => trim((string)$row['student_address']),
                            'gender'       => trim(strtolower((string)$row['student_gender'])),
                            'nationality'  => trim(strtolower((string)($row['student_nationality'] ?? 'syrian'))),
                        ],
                        'enrollment' => [
                            'academic_year_id' => $yearId,
                            'grade_level_id'   => $gradeId,
                            'class_room_id'    => $roomId,
                        ]
                    ];

                    $studentService->registerStudentWithGuardian($payload);
                    $successCount++;

                } catch (Throwable $e) {
                    $failedCount++;
                    ImportError::create([
                        'import_batch_id' => $batch->id,
                        'row_number'      => $rowNumber,
                        'row_data'        => $row,
                        'error_message'   => $e->getMessage(),
                    ]);
                }

                if ($processedCount % 20 === 0) {
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

            Storage::disk('local')->delete($batch->file_path);

        } catch (Throwable $criticalError) {
            $batch->update(['status' => 'failed']);
        }
    }
}