<?php

namespace App\Services\Student;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Guardian;
use Exception;
use Illuminate\Database\Eloquent\Builder;


class StudentManagementService
{

public function filterStudents(array $filters)
{
 $query = Enrollment::withTrashed()->with([
            'student.user:id,first_name,last_name,father_name,mother_name,phone_number', 
            'gradeLevel', 
            'classRoom'
        ]);

        if (isset($filters['level'])) {
            $query->whereHas('gradeLevel', function ($q) use ($filters) {
                $q->where('level', $filters['level']);
            });
        }

        if (!empty($filters['classroom_name'])) {
            $query->whereHas('classRoom', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['classroom_name']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('enrollment_status', $filters['status']);
        }

        $direction = (isset($filters['sort']) && strtolower($filters['sort']) === 'desc') ? 'desc' : 'asc';
        
        $query->join('students', 'enrollments.student_id', '=', 'students.id')
              ->join('users', 'students.user_id', '=', 'users.id')
              ->select('enrollments.*') 
              ->orderBy('users.first_name', $direction)
              ->orderBy('users.father_name', $direction)
              ->orderBy('users.last_name', $direction);

        return $query->paginate(15);
}
 public function searchStudents(string $searchTerm)
    {
       $query = Enrollment::withTrashed()->with([
            'student.user:id,first_name,last_name,father_name,mother_name,phone_number', 
            'gradeLevel', 
            'classRoom'
        ]);

        $safe = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);
        
        $query->whereHas('student.user', function ($q) use ($safe) {
            $q->where(DB::raw("CONCAT(first_name, ' ', father_name, ' ', last_name)"), 'like', "%{$safe}%")
              ->orWhere('first_name', 'like', "%{$safe}%")
              ->orWhere('father_name', 'like', "%{$safe}%")
              ->orWhere('last_name', 'like', "%{$safe}%");
        });

        $query->join('students', 'enrollments.student_id', '=', 'students.id')
              ->join('users', 'students.user_id', '=', 'users.id')
              ->select('enrollments.*')
              ->orderBy('users.first_name', 'asc')
              ->orderBy('users.father_name', 'asc')
              ->orderBy('users.last_name', 'asc');

        return $query->paginate(15);
    }
   public function getStudentPersonalProfile($studentId)
    {
        $student = Student::withTrashed()->with([
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
        $enrollment = Enrollment::withTrashed()->with([
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