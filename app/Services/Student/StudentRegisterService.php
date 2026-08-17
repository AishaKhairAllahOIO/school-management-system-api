<?php

namespace App\Services\Student;

use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\ImportBatch;
use App\Models\GradeConfiguration;
use App\Models\Classroom;
use App\Jobs\ProcessStudentsImportJob;
use App\Models\DeviceToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ImportError;
use Rap2hpoutre\FastExcel\FastExcel;
use Exception;
use App\Models\FinancialAccount;
use DateTimeInterface;

class StudentRegisterService
{
    public function registerStudentWithGuardian(array $data)
    {
        return DB::transaction(function () use ($data) {


            $academicYearId = $data['enrollment']['academic_year_id'];
            $gradeId = $data['enrollment']['grade_level_id'];
            $classroomId = $data['enrollment']['class_room_id'] ?? null;

            $gradeConfig = GradeConfiguration::where('academic_year_id', $academicYearId)
                ->where('grade_level_id', $gradeId)
                ->first();

            if (!$gradeConfig) {
                throw new Exception('Registration is not open, or no capacity plan is defined for this grade in the selected academic year.', 422);
            }

            if ($classroomId) {
                $classroom = Classroom::findOrFail($classroomId);

                $classroomGradeId = $classroom->grade_level_id ?? $classroom->grade_id;

                if ($classroom->academic_year_id != $academicYearId || $classroomGradeId != $gradeId) {
                    throw new Exception("The specified classroom ({$classroom->name}) does not belong to the student's selected grade or academic year.", 422);
                }
                if ($classroom->available_seats <= 0) {
                    throw new Exception("The classroom ({$classroom->name}) is fully occupied. No seats are available.", 422);
                }
            } else {
                $currentEnrolledCount = Enrollment::where('academic_year_id', $academicYearId)
                    ->where('grade_level_id', $gradeId)
                    ->whereIn('enrollment_status', ['completed', 'enrolled'])
                    ->count();

                if ($currentEnrolledCount >= $gradeConfig->planned_students_capacity) {
                    throw new Exception('The total allowed capacity for this grade has been reached. No more students can be registered.', 422);
                }
            }

            $guardianPhotoPath = 'defaults/guardian.png';
            if (isset($data['guardian']['photo_url']) && $data['guardian']['photo_url'] instanceof UploadedFile) {
                $guardianPhotoPath = $data['guardian']['photo_url']
                    ->store('users/guardians', config('filesystems.default'));
            }

            $studentPhotoPath = 'defaults/student.png';
            if (isset($data['student']['photo_url']) && $data['student']['photo_url'] instanceof UploadedFile) {
                $studentPhotoPath = $data['student']['photo_url']
                    ->store('users/students', config('filesystems.default'));
            }


            $guardianPhone = $data['guardian']['phone_number'];
            $guardianUser = User::where('phone_number', $guardianPhone)->first();

            if (!$guardianUser) {
                $guardianUser = User::create([
                    'first_name' => $data['guardian']['first_name'],
                    'last_name' => $data['guardian']['last_name'],
                    'father_name' => $data['guardian']['father_name'] ?? 'غير مدخل',
                    'mother_name' => $data['guardian']['mother_name'] ?? 'غير مدخل',
                    'birth_date' => $data['guardian']['birth_date'] instanceof DateTimeInterface
                        ? $data['guardian']['birth_date']->format('Y-m-d')
                        : $data['guardian']['birth_date'],
                    'birth_place' => $data['guardian']['birth_place'] ?? 'غير مدخل',
                    'address' => $data['guardian']['address'],
                    'gender' => $data['guardian']['gender'],
                    'nationality' => $data['guardian']['nationality'] ?? 'syrian',
                    'phone_number' => $guardianPhone,
                    'email' => null,
                    'password' => env('DEFAULT_USER_PASSWORD', 'password'),
                    'photo_url' => $guardianPhotoPath ?? 'defaults/guardian.png',
                    'account_status' => 'enabled',
                    'record_status' => 'active',
                ]);

                $guardianUser->assignRole('guardian');
                $guardianRecord = Guardian::create(['user_id' => $guardianUser->id]);
            } else {
                $guardianRecord = Guardian::firstOrCreate(['user_id' => $guardianUser->id]);

                if (!$guardianUser->hasRole('guardian')) {
                    $guardianUser->assignRole('guardian');
                }

                if (isset($data['guardian']['photo_url']) && $data['guardian']['photo_url'] instanceof UploadedFile) {
                    if ($guardianUser->photo_url && !str_starts_with($guardianUser->photo_url, 'defaults/')) {
                        Storage::disk(config('filesystems.default'))
                            ->delete($guardianUser->photo_url);
                    }
                    $guardianUser->update(['photo_url' => $guardianPhotoPath]);
                }
            }

            $temporaryFcmToken = $data['guardian']['token_fcm'] ?? null;

            if (!empty($temporaryFcmToken) && $temporaryFcmToken !== "fYJpl4_2tzFTIBwb7wPXVk:APA91bF0jrkAZwR8K1ETdEfiSG6JHyD03n-i12twY-qZgVpcSOWKqNMw3GrjlsFtn_n85xkuDIYfnQ83rk7dvvoaEJnh_X7sZ5AYjzK9sby2GZ8bm6PMncQ") {
                DeviceToken::updateOrCreate(
                    ['fcm_token' => $temporaryFcmToken],
                    ['user_id' => $guardianUser->id]
                );
            }

            $studentUser = User::create([
                'first_name' => $data['student']['first_name'],
                'last_name' => $data['student']['last_name'],
                'father_name' => $data['student']['father_name'],
                'mother_name' => $data['student']['mother_name'],
                'birth_date' => $data['student']['birth_date'] instanceof \DateTimeInterface
                    ? $data['student']['birth_date']->format('Y-m-d')
                    : $data['student']['birth_date'],
                'birth_place' => $data['student']['birth_place'],
                'address' => $data['student']['address'],
                'gender' => $data['student']['gender'],
                'nationality' => $data['student']['nationality'],
                'phone_number' => $data['student']['phone_number'],
                'photo_url' => $studentPhotoPath ?? 'defaults/student.png',
                'email' => null,
                'password' => env('DEFAULT_USER_PASSWORD', 'password'),
                'account_status' => 'disabled',
                'record_status' => 'active',
            ]);

            $studentUser->assignRole('student');

            $studentRecord = Student::create([
                'user_id' => $studentUser->id,
                'guardian_id' => $guardianRecord->id,
            ]);

            Enrollment::create([
                'student_id' => $studentRecord->id,
                'academic_year_id' => $academicYearId,
                'grade_level_id' => $gradeId,
                'class_room_id' => $classroomId,
                'enrollment_status' => 'suspended',
            ]);

            FinancialAccount::create([
                'student_id' => $studentRecord->id,
                'academic_year_id' => $academicYearId,
            ]);

            $enrollment = Enrollment::where('student_id', $studentRecord->id)
                ->with([
                    'student.user',
                    'student.guardian.user',
                    'gradeLevel',
                    'classRoom',
                    'academicYear'
                ])
                ->latest()
                ->first();

            return $enrollment;
        });
    }

    public function initiateExcelImport(UploadedFile $file, int $importerId)
    {
        $storedPath = $file->store(
            'temp_imports',
            config('filesystems.default')
        );
        $batch = ImportBatch::create([
            'batch_title' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'imported_by_user_id' => $importerId,
            'status' => 'pending'
        ]);

        ProcessStudentsImportJob::dispatch($batch->id);

        return $batch;
    }

    public function downloadBatchErrors(ImportBatch $batch)
    {
        $errors = ImportError::where('import_batch_id', $batch->id)->get();

        if ($errors->isEmpty()) {
            throw new Exception('No errors were recorded for this batch.');
        }

        $exportData = $errors->map(function ($errorRecord) {
            $originalRow = is_array($errorRecord->row_data)
                ? $errorRecord->row_data
                : json_decode($errorRecord->row_data, true);

            return array_merge([
                'EXCEL_ROW_NUMBER' => $errorRecord->row_number,
                'REJECTION_REASON' => $errorRecord->error_message,
            ], $originalRow ?? []);
        });

        return (new FastExcel($exportData))->download("rejected_students_batch_{$batch->id}.xlsx");
    }

    public function getImportBatchesArchive(array $filters)
    {
        $query = ImportBatch::latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $safeSearch = str_replace(['%', '_'], ['\%', '\_'], $filters['search']);
            $query->where('batch_title', 'like', "%{$safeSearch}%");
        }

        if (!empty($filters['importer_id'])) {
            $query->where('imported_by_user_id', $filters['importer_id']);
        }

        return $query->get();
    }
}
