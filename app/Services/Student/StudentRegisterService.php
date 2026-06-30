<?php

namespace App\Services\Student;

use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\ImportBatch;
use App\Jobs\ProcessStudentsImportJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\ImportError;
use Rap2hpoutre\FastExcel\FastExcel;

class StudentRegisterService
{
    public function registerStudentWithGuardian(array $data): User
    {
        return DB::transaction(function () use ($data) {
            
            // 1. حساب الأب
            $guardianPhone = $data['guardian']['phone_number'];
            $guardianUser  = User::where('phone_number', $guardianPhone)->first();

            if (!$guardianUser) {
                $guardianUser = User::create([
                    'first_name'     => $data['guardian']['first_name'],
                    'last_name'      => $data['guardian']['last_name'],
                    'father_name'    => $data['guardian']['father_name'] ?? 'غير مدخل',
                    'mother_name'    => $data['guardian']['mother_name'] ?? 'غير مدخل',
                    'birth_date'     => $data['guardian']['birth_date'] instanceof \DateTimeInterface 
                                        ? $data['guardian']['birth_date']->format('Y-m-d') 
                                        : $data['guardian']['birth_date'],
                    'birth_place'    => $data['guardian']['birth_place'] ?? 'غير مدخل',
                    'address'        => $data['guardian']['address'],
                    'gender'         => $data['guardian']['gender'],  
                    'nationality'    => $data['guardian']['nationality'] ?? 'syrian',
                    'phone_number'   => $guardianPhone,
                    'email'          => null, 
                    'password'       => env('DEFAULT_USER_PASSWORD', 'password'),
                    'photo_url'      => $data['guardian']['photo_url'] ?? 'defaults/guardian.png',
                    'account_status' => 'enabled',
                    'record_status'  => 'active',
                ]);

                $guardianUser->assignRole('guardian');
                $guardianRecord = Guardian::create(['user_id' => $guardianUser->id]);
            } else {
                $guardianRecord = $guardianUser->guardian ?? Guardian::create(['user_id' => $guardianUser->id]);
                if (!$guardianUser->hasRole('guardian')) $guardianUser->assignRole('guardian');
            }

            // 2. حساب الطالب
            $studentUser = User::create([
                'first_name'     => $data['student']['first_name'],
                'last_name'      => $data['student']['last_name'],
                'father_name'    => $data['student']['father_name'],
                'mother_name'    => $data['student']['mother_name'],
                'birth_date'     => $data['student']['birth_date'] instanceof \DateTimeInterface 
                                    ? $data['student']['birth_date']->format('Y-m-d') 
                                    : $data['student']['birth_date'],
                'birth_place'    => $data['student']['birth_place'],
                'address'        => $data['student']['address'],
                'gender'         => $data['student']['gender'],
                'nationality'    => $data['student']['nationality'],                
                'phone_number'   => $data['student']['phone_number'],
                'photo_url'      => $data['student']['photo_url'] ?? 'defaults/student.png',
                'email'          => null,
                'password'       => env('DEFAULT_USER_PASSWORD', 'password'),
                'account_status' => 'disabled', // معطل حتى يتم الدفع
                'record_status'  => 'active',
            ]);

            $studentUser->assignRole('student');

            $studentRecord = Student::create([
                'user_id'        => $studentUser->id,
                'guardian_id'    => $guardianRecord->id,
            ]);

            // 3. توثيق الالتحاق (الداتابيز ستعطيه حالة suspended افتراضياً)
            Enrollment::create([
                'student_id'       => $studentRecord->id,
                'academic_year_id' => $data['enrollment']['academic_year_id'],
                'grade_level_id'   => $data['enrollment']['grade_level_id'],
                'class_room_id'    => $data['enrollment']['class_room_id'] ?? null,
            ]);

            return $studentUser->fresh(['student.guardian.user', 'student.enrollments']);
        });
    }

    public function initiateExcelImport(UploadedFile $file, int $importerId)
    {
        $storedPath = $file->store('temp_imports', 'local');

        $batch = ImportBatch::create([
            'batch_title'         => $file->getClientOriginalName(),
            'file_path'           => $storedPath,
            'imported_by_user_id' => $importerId,
            'status'              => 'pending'
        ]);

        ProcessStudentsImportJob::dispatch($batch->id);

        return $batch;
    }

    public function downloadBatchErrors(ImportBatch $batch)
    {
        $errors = ImportError::where('import_batch_id', $batch->id)->get();

        if ($errors->isEmpty()) {
            throw new \Exception('لا توجد أخطاء مسجلة لهذه الدفعة.');
        }

        $exportData = $errors->map(function ($errorRecord) {
            $originalRow = is_array($errorRecord->row_data) 
                ? $errorRecord->row_data 
                : json_decode($errorRecord->row_data, true);

            return array_merge([
                'EXCEL_ROW_NUMBER'  => $errorRecord->row_number,
                'REJECTION_REASON'  => $errorRecord->error_message,
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

        return $query->paginate($filters['per_page'] ?? 15);
    }
}