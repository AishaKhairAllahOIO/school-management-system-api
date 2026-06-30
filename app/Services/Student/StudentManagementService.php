<?php

namespace App\Services\Student;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentManagementService
{
    /**
     * عقل دالة الـ Index: يدمج البحث، الفلترة بالصف، الفلترة بالشعبة، والترتيب
     */
    public function getAllStudents(array $filters)
    {
        // نسحب الطالب مع حسابه البشري (user) وحسابه المالي وحالته الأكاديمية
        $query = Student::with(['user:id,first_name,last_name,father_name,mother_name,phone_number,record_status', 'enrollments.gradeLevel', 'enrollments.classRoom']);

        // 1. بحث بالاسم الأول أو الأخير أو اسم الأب (FR-12)
        if (!empty($filters['search'])) {
            $safe = str_replace(['%', '_'], ['\%', '\_'], $filters['search']);
            $query->whereHas('user', function ($q) use ($safe) {
                $q->where('first_name', 'like', "%{$safe}%")
                  ->orWhere('last_name', 'like', "%{$safe}%")
                  ->orWhere('father_name', 'like', "%{$safe}%");
            });
        }

        // 2. فلترة حسب الصف الدراسي (FR-09)
        if (!empty($filters['grade_level_id'])) {
            $query->whereHas('enrollments', fn($q) => $q->where('grade_level_id', $filters['grade_level_id']));
        }

        // 3. فلترة حسب الشعبة الدراسية (FR-10)
        if (!empty($filters['class_room_id'])) {
            $query->whereHas('enrollments', fn($q) => $q->where('class_room_id', $filters['class_room_id']));
        }

        // 4. الترتيب أبجدياً أو حسب الأحدث (FR-13)
        $dir = (!empty($filters['sort']) && strtolower($filters['sort']) === 'desc') ? 'desc' : 'asc';
        
        // نرتب استناداً إلى اسم المستخدم في الجدول المرتبط
        $query->join('users', 'students.user_id', '=', 'users.id')
              ->orderBy('users.first_name', $dir)
              ->select('students.*');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function getStudentById($id)
    {
        $student = Student::with(['user', 'guardian.user', 'enrollments.academicYear', 'enrollments.gradeLevel', 'enrollments.classRoom'])->find($id);

        if (!$student) {
            throw new \Exception('الطالب غير موجود في سجلات المدرسة.', 404);
        }

        return $student;
    }

    public function updateStudent($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $student = Student::findOrFail($id);
            
            // تحديث بيانات جدول المستهلك العام (users)
            if (isset($data['user'])) {
                $student->user()->update($data['user']);
            }

            // تحديث البيانات المدرسية الخاصة
            $studentData = array_diff_key($data, ['user' => '']);
            if (!empty($studentData)) {
                $student->update($studentData);
            }

            return $student->fresh(['user', 'enrollments']);
        });
    }

public function deleteStudent($id)
    {
        return DB::transaction(function () use ($id) {
            $student = Student::findOrFail($id);
            
            // 1. شل الحساب البشري تماماً
            if ($student->user) {
                $student->user->update(['account_status' => 'disabled']);
                $student->user->delete(); 
            }

            // 2. تصفية القيود الأكاديمية (نحول حالته إلى 'canceled' أو 'withdrawn')
            // هذا يحرر المقعد في الشعبة
            $student->enrollments()->update(['status' => 'canceled']);

            // 3. تجميد المحفظة المالية (لكي لا تظهر في ديون المدرسة النشطة)
            // ملاحظة: لا نحذف المحفظة لنحتفظ بتاريخ ما دفعه سابقاً
            if ($student->financialAccount) {
                $student->financialAccount()->update(['payment_status' => 'canceled']); 
            }

            // 4. أخيراً: إخفاء جثة الطالب (Soft Delete)
            $student->delete();

            return true;
        });
    }


    public function toggleAccountStatus($id)
    {
        $student = Student::with('user')->findOrFail($id);
        $user = $student->user;

        $newStatus = ($user->account_status === 'enabled') ? 'disabled' : 'enabled';
        
        $user->update(['account_status' => $newStatus]);

        return $newStatus;
    }
}