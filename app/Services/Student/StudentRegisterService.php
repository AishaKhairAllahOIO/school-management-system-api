<?php
namespace App\Services\Student;

use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentRegisterService
{
    public function registerStudentWithGuardian(array $data): User
    {
        return DB::transaction(function () use ($data) {
            
            // 1. حساب الأب (يُفعّل فوراً ليدفع الفاتورة)
            $guardianPhone = $data['guardian']['phone_number'];
            $guardianUser  = User::where('phone_number', $guardianPhone)->first();

            if (!$guardianUser) {
                $guardianUser = User::create([
                    'first_name'     => $data['guardian']['first_name'],
                    'last_name'      => $data['guardian']['last_name'],
                    'father_name'    => $data['guardian']['father_name'] ?? 'غير مدخل',
                    'mother_name'    => $data['guardian']['mother_name'] ?? 'غير مدخل',
                    'birth_date'     => $data['guardian']['birth_date']  ?? '1980-01-01',
                    'birth_place'    => $data['guardian']['birth_place'] ?? 'غير مدخل',
                    'address'        => $data['guardian']['address'],
                    'gender'         => 'male',
                    'nationality'    => 'syrian',
                    'phone_number'   => $guardianPhone,
                    'email'          => null, 
                    'password'       => Hash::make('12345678'), 
                    'photo_url'      => 'defaults/guardian.png',
                    'account_status' => 'enabled', // مفعّل ليدخل للبوابة المالية
                    'record_status'  => 'active',
                ]);

                $guardianUser->assignRole('guardian');
                $guardianRecord = Guardian::create(['user_id' => $guardianUser->id]);
            } else {
                $guardianRecord = $guardianUser->guardian ?? Guardian::create(['user_id' => $guardianUser->id]);
                if (!$guardianUser->hasRole('guardian')) $guardianUser->assignRole('guardian');
            }

            // 2. حساب الطالب (مقفل برمجياً حتى إشعار مالي آخر!)
            $studentUser = User::create([
                'first_name'     => $data['student']['first_name'],
                'last_name'      => $data['student']['last_name'],
                'father_name'    => $data['student']['father_name'],
                'mother_name'    => $data['student']['mother_name'],
                'birth_date'     => $data['student']['birth_date'],
                'birth_place'    => $data['student']['birth_place'],
                'address'        => $data['student']['address'],
                'gender'         => $data['student']['gender'],
                'nationality'    => 'syrian',
                'phone_number'   => $data['student']['phone_number'],
                'email'          => null,
                'password'       => env('DEFAULT_USER_PASSWORD', 'password'),
                'photo_url'      => 'defaults/student.png',
                
                'account_status' => 'disabled', // <-- صيد المهندسة العبقري! (حساب مجمد)
                'record_status'  => 'active',
            ]);

            $studentUser->assignRole('student');

            $studentRecord = Student::create([
                'user_id'        => $studentUser->id,
                'guardian_id'    => $guardianRecord->id,
                'connect_number' => $data['connect_number'] ?? null,
            ]);

            // 4. توثيق الالتحاق بـ [حالات إجبارية من السيرفر]
            Enrollment::create([
                'student_id'       => $studentRecord->id,
                'academic_year_id' => $data['enrollment']['academic_year_id'],
                'grade_level_id'   => $data['enrollment']['grade_level_id'],
                'class_room_id'    => $data['enrollment']['class_room_id'],
                
                'enrollment_status' => 'pending',     // محقونة آلياً (قيد انتظار الدفع)
                'academic_result'   => 'under_study', // محقونة آلياً (طالب مستجد)
            ]);

            return $studentUser->fresh(['student.guardian.user', 'student.enrollments']);
        });
    }
}