<?php

namespace App\Services\Student;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Guardian;
use Exception;

class StudentManagementService
{
    /**
     * عقل دالة الـ Index: يدمج البحث، الفلترة بالصف، الفلترة بالشعبة، والترتيب
     */
/**
     * عقل دالة الـ Index: يدمج البحث، الفلترة بالصف، الفلترة بالشعبة، والترتيب
     */
public function getAllStudents(array $filters)
{
    // 1. استخدام eager loading
    $query = Student::with([
        'user:id,first_name,last_name,father_name,mother_name,phone_number', 
        'enrollments.gradeLevel', // تأكدي من تطابق اسم العلاقة في الموديل
        'enrollments.classRoom'
    ]);

    // 2. الفلترة حسب الاسم الثلاثي
    if (!empty($filters['search'])) {
        $safe = str_replace(['%', '_'], ['\%', '\_'], $filters['search']);
        $query->whereHas('user', function ($q) use ($safe) {
            // البحث الشامل في الاسم الأول + اسم الأب + الكنية
            $q->where(DB::raw("CONCAT(first_name, ' ', father_name, ' ', last_name)"), 'like', "%{$safe}%")
              ->orWhere('first_name', 'like', "%{$safe}%")
              ->orWhere('father_name', 'like', "%{$safe}%")
              ->orWhere('last_name', 'like', "%{$safe}%");
        });
    }

    // 3. الفلترة حسب الصف
    if (!empty($filters['grade_level_id'])) {
        $query->whereHas('enrollments', fn($q) => $q->where('grade_level_id', $filters['grade_level_id']));
    }

    // 4. الفلترة حسب الشعبة
    if (!empty($filters['class_room_id'])) {
        $query->whereHas('enrollments', fn($q) => $q->where('class_room_id', $filters['class_room_id']));
    }

    // 5. الترتيب الأبجدي حسب الاسم الثلاثي
    $dir = (!empty($filters['sort']) && strtolower($filters['sort']) === 'desc') ? 'desc' : 'asc';
    
    $query->join('users', 'students.user_id', '=', 'users.id')
          ->orderBy('users.first_name', $dir)
          ->orderBy('users.father_name', $dir)
          ->orderBy('users.last_name', $dir)
          ->select('students.*'); 

    // بدلاً من paginate نستخدم get لجلب كافة النتائج
    return $query->get();
}
   public function getStudentPersonalProfile($studentId)
    {
        $student = Student::with([
            'user', 
            'guardian.user'
        ])->find($studentId);

        if (!$student) {
            throw new Exception('الطالب غير موجود في النظام.', 404);
        }

        return $student;
    }

   
    public function getStudentFullProfile($enrollmentId)
    {
        $enrollment = Enrollment::with([
            'student.user',
            'student.guardian.user',
            'gradeLevel',
            'classRoom',
            'academicYear'
        ])->find($enrollmentId);

        if (!$enrollment) {
            throw new Exception('سجل التسجيل الأكاديمي غير موجود.', 404);
        }

        return $enrollment;
    }


    public function updateStudentPersonalData($studentId, array $userData)
    {
        
        return DB::transaction(function () use ($studentId, $userData) {
            
            $student = Student::with('user')->findOrFail($studentId);
            $user = $student->user;

            if (isset($userData['photo_url']) && $userData['photo_url'] instanceof \Illuminate\Http\UploadedFile) {
                
                if ($user->photo_url && !str_contains($user->photo_url, 'defaults/')) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo_url)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo_url);
                    }
                }
                $userData['photo_url'] = $userData['photo_url']->store('users/students', 'public');
                unset($userData['photo_url']); 
            }

            $user->update($userData);
            return $student->fresh('user');
        });
    }

    public function updateEnrollmentData($enrollmentId, array $enrollmentData)
    {
        return DB::transaction(function () use ($enrollmentId, $enrollmentData) {
            $enrollment = Enrollment::findOrFail($enrollmentId);
            if(!$enrollment)
                throw new Exception('سجل القيد الأكاديمي غير موجود.', 404);
            $enrollment->update($enrollmentData);
            
            return $enrollment->fresh(['gradeLevel', 'classRoom']);
        });
    }


    public function updateGuardianPersonalData($guardianId, array $guardianUserData)
    {
        return DB::transaction(function () use ($guardianId, $guardianUserData) {
            $guardian = Guardian::with('user')->findOrFail($guardianId);
            if(!$guardian)
                throw new Exception('ولي الامر غير موجود',404);
            $guardian->user()->update($guardianUserData);
            
            return $guardian->fresh('user');
        });
    }

public function deleteStudent($enrollmentId)
    {
        return DB::transaction(function () use ($enrollmentId) {
            $enrollment = Enrollment::with('student.user')->findOrFail($enrollmentId);
            
            $enrollment->update(['enrollment_status' => 'suspended']);

            if ($enrollment->student && $enrollment->student->user) {
                $enrollment->student->user->update(['account_status' => 'disabled']);
            }

            $enrollment->delete();

            return true;
        });
    }

    public function toggleAccountStatus($enrollmentId)
    {
        $enrollment = Enrollment::with('student.user')->findOrFail($enrollmentId);
        if(!$enrollment)
            throw new Exception('سجل القيد الأكاديمي غير موجود.', 404);
        $user = $enrollment->student->user;

        $newStatus = ($user->account_status === 'enabled') ? 'disabled' : 'enabled';
        $user->update(['account_status' => $newStatus]);

        return $newStatus;
    }
}