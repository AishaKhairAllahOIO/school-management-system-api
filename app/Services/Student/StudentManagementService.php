<?php

namespace App\Services\Student;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Guardian;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    public function getStudentPersonalProfile(int $studentId)
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


    public function updateStudentPersonalData(int $studentId, array $data)
    {

        return DB::transaction(function () use ($studentId, $data) {

            $student = Student::with(['user', 'guardian.user'])->findOrFail($studentId);

            if (isset($data['photo_url']) && $data['photo_url'] instanceof UploadedFile) {
                if ($student->user->photo_url && !str_contains($student->user->photo_url, 'defaults/')) {
                    if (Storage::disk('local')->exists($student->user->photo_url)) {
                        Storage::disk('local')->delete($student->user->photo_url);
                    }
                }
                $data['photo_url'] = $data['photo_url']->store('users/students', 'local');
            }

            $studentUserFields = [
                'first_name',
                'last_name',
                'father_name',
                'mother_name',
                'birth_date',
                'birth_place',
                'address',
                'gender',
                'nationality',
                'photo_url',
                'phone_number',
                'email'
            ];
            $studentUserData = array_intersect_key($data, array_flip($studentUserFields));
            if (!empty($studentUserData)) {
                $student->user->update($studentUserData);
            }

            if ($student->guardian && $student->guardian->user) {
                $guardianUserFields = [
                    'first_name',
                    'last_name',
                    'father_name',
                    'mother_name',
                    'birth_date',
                    'birth_place',
                    'address',
                    'gender',
                    'nationality',
                    'phone_number',
                    'email',
                    'national_id'
                ];

                $guardianData = [];
                foreach ($guardianUserFields as $field) {
                    $requestKey = 'guardian_' . $field;
                    if (array_key_exists($requestKey, $data)) {
                        $guardianData[$field] = $data[$requestKey];
                    }
                }

                if (!empty($guardianData)) {
                    $student->guardian->user->update($guardianData);
                }
            }

            $enrollmentFields = ['class_room_id', 'grade_level_id', 'enrollment_status'];
            $enrollmentData = array_intersect_key($data, array_flip($enrollmentFields));

            $enrollment = Enrollment::where('student_id', $student->id)->latest()->first();
            if (!empty($enrollmentData) && $enrollment) {
                $enrollment->update($enrollmentData);
            }

            return Enrollment::withTrashed()->with([
                'student.user',
                'student.guardian.user',
                'gradeLevel',
                'classRoom',
                'academicYear'
            ])->findOrFail($enrollment->id);
        });
    }

    public function updateEnrollmentData($enrollmentId, array $enrollmentData)
    {
        return DB::transaction(function () use ($enrollmentId, $enrollmentData) {
            $enrollment = Enrollment::findOrFail($enrollmentId);
            if (!$enrollment)
                throw new Exception('سجل القيد الأكاديمي غير موجود.', 404);
            $enrollment->update($enrollmentData);

            return $enrollment->fresh(['gradeLevel', 'classRoom']);
        });
    }


    public function updateGuardianPersonalData($guardianId, array $guardianUserData)
    {
        return DB::transaction(function () use ($guardianId, $guardianUserData) {
            $guardian = Guardian::with('user')->findOrFail($guardianId);
            if (!$guardian)
                throw new Exception('ولي الامر غير موجود', 404);
            $guardian->user()->update($guardianUserData);

            return $guardian->fresh('user');
        });
    }

    public function deleteStudent($enrollmentId)
    {
        return DB::transaction(function () use ($enrollmentId) {
            $enrollment = Enrollment::with('student.user')->findOrFail($enrollmentId);


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
        if (!$enrollment)
            throw new Exception('سجل القيد الأكاديمي غير موجود.', 404);
        $user = $enrollment->student->user;

        $newStatus = ($user->account_status === 'enabled') ? 'disabled' : 'enabled';
        $user->update(['account_status' => $newStatus]);

        return $newStatus;
    }
    public function restoreStudent(int $enrollmentId)
    {
        return DB::transaction(function () use ($enrollmentId) {

            $enrollment = Enrollment::withTrashed()->with('student.user')->findOrFail($enrollmentId);

            if (!$enrollment->trashed()) {
                throw new Exception('هذا القيد غير محذوف أصلاً لكي يتم استرجاعُه.', 422);
            }

            $enrollment->restore();
         //   $enrollment->update(['enrollment_status' => 'confirmed']);

            // 3. إعادة تفعيل حساب المستخدم (User) الخاص بالطالب
            if ($enrollment->student && $enrollment->student->user) {
                $enrollment->student->user->update(['account_status' => 'enabled']);
            }

            // 4. إرجاع السجل كاملاً مع علاقاته ليعرضه الـ Resource بالشكل السليم
            return Enrollment::with([
                'student.user',
                'student.guardian.user',
                'gradeLevel',
                'classRoom',
                'academicYear'
            ])->findOrFail($enrollment->id);
        });
    }
}
