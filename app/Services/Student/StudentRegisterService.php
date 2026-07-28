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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\ImportError;
use Rap2hpoutre\FastExcel\FastExcel;
use Exception;
use App\Models\FinancialAccount;

class StudentRegisterService
{
    public function registerStudentWithGuardian(array $data)
    {
        return DB::transaction(function () use ($data) {

            // =========================================================
            // 🛡️ طبقة الحماية الاستباقية (Capacity Validation)
            // =========================================================
            $academicYearId = $data['enrollment']['academic_year_id'];
            $gradeId = $data['enrollment']['grade_level_id'];
            $classroomId = $data['enrollment']['class_room_id'] ?? null;

            // 1. هل المدرسة فتحت التسجيل لهذا الصف في هذا العام أساساً؟
            $gradeConfig = GradeConfiguration::where('academic_year_id', $academicYearId)
                ->where('grade_level_id', $gradeId)
                ->first();

            if (!$gradeConfig) {
                throw new Exception('عذراً، لم تقم الإدارة بفتح التسجيل أو تحديد خطة استيعابية لهذا الصف في العام الدراسي المحدد.', 422);
            }

            // 2. فحص سعة الشعبة (إذا تم تحديد شعبة للطالب)
            if ($classroomId) {
                $classroom = Classroom::findOrFail($classroomId);

                $classroomGradeId = $classroom->grade_level_id ?? $classroom->grade_id; // دعم للمسميين

                if ($classroom->academic_year_id != $academicYearId || $classroomGradeId != $gradeId) {
                    throw new Exception("عذراً، الشعبة المحددة ({$classroom->name}) لا تنتمي للصف أو العام الدراسي المحدد للطالب.", 422);
                }
                // استخدام الـ Accessor الذكي
                if ($classroom->available_seats <= 0) {
                    throw new Exception("عذراً، الشعبة ({$classroom->name}) ممتلئة بالكامل ولا توجد مقاعد متاحة.", 422);
                }
            } else {
                // 3. فحص السعة الكلية للصف (إذا لم يتم تحديد شعبة، سيتم فرزه لاحقاً)
                $currentEnrolledCount = Enrollment::where('academic_year_id', $academicYearId)
                    ->where('grade_level_id', $gradeId)
                    ->whereIn('enrollment_status', ['completed', 'enrolled'])
                    ->count();

                if ($currentEnrolledCount >= $gradeConfig->planned_students_capacity) {
                    throw new Exception('عذراً، لقد اكتمل العدد الكلي المسموح به لهذا الصف ولا يمكن تسجيل المزيد من الطلاب.', 422);
                }
            }
            $guardianPhotoPath = 'defaults/guardian.png';
            if (isset($data['guardian']['photo_url']) && $data['guardian']['photo_url'] instanceof UploadedFile) {
                $guardianPhotoPath = $data['guardian']['photo_url']->store('users/guardians', 'local');
            }

            $studentPhotoPath = 'defaults/student.png';
            if (isset($data['student']['photo_url']) && $data['student']['photo_url'] instanceof UploadedFile) {
                $studentPhotoPath = $data['student']['photo_url']->store('users/students', 'local');
            }


            $guardianPhone = $data['guardian']['phone_number'];
            $guardianUser = User::where('phone_number', $guardianPhone)->first();

            if (!$guardianUser) {
                $guardianUser = User::create([
                    'first_name' => $data['guardian']['first_name'],
                    'last_name' => $data['guardian']['last_name'],
                    'father_name' => $data['guardian']['father_name'] ?? 'غير مدخل',
                    'mother_name' => $data['guardian']['mother_name'] ?? 'غير مدخل',
                    'birth_date' => $data['guardian']['birth_date'] instanceof \DateTimeInterface
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
            }
            if (!empty($temporaryFcmToken) && $temporaryFcmToken !== "fYJpl4_2tzFTIBwb7wPXVk:APA91bF0jrkAZwR8K1ETdEfiSG6JHyD03n-i12twY-qZgVpcSOWKqNMw3GrjlsFtn_n85xkuDIYfnQ83rk7dvvoaEJnh_X7sZ5AYjzK9sby2GZ8bm6PMncQ") {
                \App\Models\DeviceToken::updateOrCreate(
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
                'account_status' => 'disabled', // معطل حتى يتم الدفع
                'record_status' => 'active',
            ]);

            $studentUser->assignRole('student');

            $studentRecord = Student::create([
                'user_id' => $studentUser->id,
                'guardian_id' => $guardianRecord->id,
            ]);

            // =========================================================
            // 📝 3. توثيق الالتحاق (Enrollment)
            // =========================================================
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

    return $enrollment;        });
    }

    public function initiateExcelImport(UploadedFile $file, int $importerId)
    {
        $storedPath = $file->store('temp_imports', 'local');

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
            throw new \Exception('لا توجد أخطاء مسجلة لهذه الدفعة.');
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
