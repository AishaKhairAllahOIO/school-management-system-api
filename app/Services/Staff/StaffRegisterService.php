<?php

namespace App\Services\Staff;

use App\Models\User;
use App\Models\Staff;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Jobs\ProcessStaffImportJob;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\ImportError;

class StaffRegisterService
{
   
    public function registerSingleStaff(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            
            $photoPath = 'defaults/staff.png'; 
            if (isset($data['photo_url']) && $data['photo_url'] instanceof UploadedFile) {
                $photoPath = $data['photo_url']->store('users/staff', 'public');
            }

            $user = User::create([
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'father_name'    => $data['father_name'],
                'mother_name'    => $data['mother_name'],
                'birth_date'     => $data['birth_date'],
                'birth_place'    => $data['birth_place'],
                'address'        => $data['address'],
                'gender'         => $data['gender'],
                'email'          =>$data['email'],
                'nationality'    => $data['nationality'] ?? 'syrian',
                'phone_number'   => $data['phone_number'],
                'photo_url'      => $photoPath,
                'password'       => bcrypt(env('DEFAULT_USER_PASSWORD', 'password')),
                'account_status' => 'enabled', 
                'record_status'  => 'active',
            ]);

            //$user->assignRole('staff');

            $staff = Staff::create([
                'user_id'          => $user->id,
                'degree'           => $data['degree'],
                'specialization'   => $data['specialization'],
                'university'       => $data['university'],
                'graduation_year'  => $data['graduation_year'],
                'hire_date'        => $data['hire_date'],
                'experience_years' => $data['experience_years'] ?? 0,
            ]);

            return $staff->load('user');
        });
    }


    public function initiateStaffExcelImport(UploadedFile $file, int $adminId): ImportBatch
    {
        $filePath = $file->storeAs(
            'imports/staff', 
            'staff_import_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        $batch = ImportBatch::create([
            'batch_title'         => $file->getClientOriginalName(),
            'file_path'           => $filePath,
            'imported_by_user_id' => $adminId, // ✅ تم التصحيح هنا: يجب أن نمرر الـ ID الخاص بالمدير   
            'status'              => 'pending'
        ]);

        ProcessStaffImportJob::dispatch($batch->id);

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

        return (new FastExcel($exportData))->download("rejected_staff_batch_{$batch->id}.xlsx");
    }
}