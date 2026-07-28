<?php
namespace App\Services\Staff;
use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffManagementService
{
    public function getStaffRoleCounts(): array
    {
        $roles = ['teacher', 'adviser', 'counselor', 'secretary', 'service_staff', 'student'];
        $counts = [];

        foreach ($roles as $role) {
            $counts[$role] = User::withTrashed()->role($role)->count();
        }
        $counts['total'] = User::withTrashed()->role($roles)->count();

        return $counts;
    }

    /**
     * 2. جلب قائمة الموظفين الذين يحملون دوراً محدداً
     */
    public function getStaffByRole(string $roleName, int $perPage = 15)
    {
        if ($roleName === 'student')
            return Student::
                whereHas('user', function ($query) use ($roleName) {
                    $query->role($roleName);
                })
                ->with([
                    'user.roles',
                    'enrollments' => function ($query) {
                        $query->withTrashed();
                    }
                ])
                ->paginate($perPage);
        return Staff::withTrashed()->
            whereHas('user', function ($query) use ($roleName) {
                $query->role($roleName);
            })
            ->with(['user.roles'])
            ->paginate($perPage);
    }

    /**
     * 3. جلب الملف الشخصي الكامل لموظف محدد
     */
    public function ownProfile()
    {
        $user = auth()->user()->staff;
        return $user->load('user.roles');

    }
    public function getStaffProfile(int $id): Staff
    {
        // تم تضمين user.roles للتعرف على الدور في الفرونت إند
        return Staff::withTrashed()->with(['user.roles'])->findOrFail($id);
    }

    public function getAllStaff($perPage = 15)
    {
        return Staff::withTrashed()->with('user')->paginate($perPage);
    }

    public function getStaffById(int $id)
    {
        $staff = Staff::withTrashed()->findOrFail($id);
        return $staff;
    }

    // public function updateStaff(Staff $staff, array $data): Staff

    // {
    //     return DB::transaction(function () use ($staff, $data) {

    //         // 1. تحديث صورة المستخدم إن وجدت
    //         if (isset($data['photo_url']) && $data['photo_url'] instanceof \Illuminate\Http\UploadedFile) {
    //             $data['user']['photo_url'] = $data['photo_url']->store('users/staff', 'public');
    //             unset($data['photo_url']);
    //         }

    //         $userData = array_intersect_key($data, array_flip(['first_name', 'last_name', 'phone_number', 'email', 'birth_date', 'address', 'photo_url']));
    //         $staffData = array_intersect_key($data, array_flip(['degree', 'specialization', 'experience_years']));

    //         if (!empty($userData)) {
    //             $staff->user()->update($userData);
    //         }
    //         if (!empty($staffData)) {
    //             $staff->update($staffData);
    //         }

    //         return $staff->refresh()->load('user');
    //     });
    // }


    public function updatePersonalData(int $id, array $data): Staff
    {
        return DB::transaction(function () use ($id, $data) {
            $staff = Staff::with('user')->findOrFail($id);

            if ($staff->trashed()) {
                throw new \Exception('لا يمكن تعديل بيانات موظف تم حذفه من النظام.');
            }

            $user = $staff->user;

            if (isset($data['photo_url']) && $data['photo_url'] instanceof \Illuminate\Http\UploadedFile) {
    if ($user->photo_url && !str_contains($user->photo_url, 'defaults/')) {
        if (Storage::disk('local')->exists($user->photo_url)) {
            Storage::disk('local')->delete($user->photo_url);
        }
    }
    $data['photo_url'] = $data['photo_url']->store('users/staff', 'local');
}

            $userFields = [
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
                'email',
                'account_status'
            ];

            $staffFields = [
                'degree',
                'specialization',
                'university',
                'graduation_year',
                'hire_date',
                'experience_years',
                'service_type'
            ];

            // استخراج وتحديث بيانات جدول users فقط
            $userData = array_intersect_key($data, array_flip($userFields));
            if (!empty($userData)) {
                $user->update($userData);
            }

            // استخراج وتحديث بيانات جدول staff فقط
            $staffData = array_intersect_key($data, array_flip($staffFields));
            if (!empty($staffData)) {
                $staff->update($staffData);
            }

            return $staff->refresh()->load('user');
        });
    }


    // public function updateEmploymentData(int $id, array $data): Staff
    // {
    //     return DB::transaction(function () use ($id, $data) {
    //     $staff = Staff::withTrashed()->findOrFail($id);
    //      if ($staff->trashed()) {
    //         throw new \Exception('لا يمكن تعديل بيانات موظف تم حذفه من النظام.');
    //     }
    //         $staff->update($data);

    //         return $staff->refresh()->load('user');
    //     });
    // }


    public function searchStaffByRoleAndName(string $roleName, string $fullName, int $perPage = 15)
    {
        return Staff::withTrashed()
            ->whereHas('user', function ($query) use ($roleName, $fullName) {
                $query->role($roleName);

                if (!empty($fullName)) {
                    $query->where(DB::raw("CONCAT(first_name, ' ', father_name, ' ', last_name)"), 'LIKE', "%{$fullName}%");
                }
            })
            ->with(['user.roles'])
            ->paginate($perPage);
    }

    public function getAllStaffAlphabetically(string $direction = 'asc', $perPage = 15)
    {
        return Staff::withTrashed()->join('users', 'staff.user_id', '=', 'users.id')
            ->select('staff.*')
            ->orderBy('users.first_name', $direction)
            ->orderBy('users.father_name', $direction)
            ->orderBy('users.last_name', $direction)
            ->with('user')
            ->paginate($perPage);
    }
    public function toggleAccountStatus(int $id)
    {
        $staff = Staff::findOrFail($id);
        $user = $staff->user;
        $newStatus = ($user->account_status === 'enabled') ? 'disabled' : 'enabled';

        $user->update(['account_status' => $newStatus]);
        return $newStatus;

    }

    public function deleteStaff(int $id): void
    {
        DB::transaction(function () use ($id) {
            $staff = Staff::findOrFail($id);
            $staff->user->update(['account_status' => 'disabled']);
            $staff->user()->delete();
            $staff->delete();
        });
    }
    public function restoreStaff(int $staffId)
    {
        return DB::transaction(function () use ($staffId) {

            // 1. البحث عن الموظف ضمن السجلات المحذوفة (Soft Deleted) باستخدام withTrashed()
            $staff = Staff::withTrashed()->with('user')->findOrFail($staffId);

            if (!$staff->trashed()) {
                throw new \Exception('هذا الموظف غير محذوف أصلاً لكي يتم استرجاعُه.', 422);
            }

            $staff->restore();

            if ($staff->user) {
                if (method_exists($staff->user, 'trashed') && $staff->user->trashed()) {
                    $staff->user->restore();
                }

                $staff->user->update([
                    'account_status' => 'enabled'
                ]);
            }

            return Staff::with(['user.roles'])->findOrFail($staff->id);
        });
    }
    


}


