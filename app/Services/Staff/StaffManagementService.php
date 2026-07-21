<?php
namespace App\Services\Staff;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class StaffManagementService{
     public function getStaffRoleCounts(): array
    {
        $roles = ['teacher', 'adviser', 'counselor', 'secretary', 'service_staff', 'super_admin'];
        $counts = [];

        foreach ($roles as $role) {
            $counts[$role] = Staff::whereHas('user', function ($query) use ($role) {
                $query->role($role);
            })->count();
        }

        $counts['total'] = Staff::count();

        return $counts;
    }

    /**
     * 2. جلب قائمة الموظفين الذين يحملون دوراً محدداً
     */
    public function getStaffByRole(string $roleName, int $perPage = 15)
    {
        return Staff::withTrashed()
            ->whereHas('user', function ($query) use ($roleName) {
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
       $user=auth()->user()->staff; 
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
        $staff=Staff::withTrashed()->findOrFail($id);
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
      $staff = Staff::withTrashed()->findOrFail($id);

        if ($staff->trashed()) {
            throw new \Exception('لا يمكن تعديل بيانات موظف تم حذفه من النظام.');
        }

        $user = $staff->user;
            // معالجة رفع الصورة الشخصية إن وجدت
            if (isset($data['photo_url']) && $data['photo_url'] instanceof \Illuminate\Http\UploadedFile) {
                $data['photo_url'] = $data['photo_url']->store('users/staff', 'public');
            }

            $user->update($data);

            return $staff->refresh()->load('user');
        });
    }

   
    public function updateEmploymentData(int $id, array $data): Staff
    {
        return DB::transaction(function () use ($id, $data) {
        $staff = Staff::withTrashed()->findOrFail($id);
         if ($staff->trashed()) {
            throw new \Exception('لا يمكن تعديل بيانات موظف تم حذفه من النظام.');
        }
            $staff->update($data);

            return $staff->refresh()->load('user');
        });
    }


    public function searchStaffByFullName(string $fullName, $perPage = 15)
    {
        return Staff::withTrashed()->whereHas('user', function ($query) use ($fullName) {
            $query->where(DB::raw("CONCAT(first_name, ' ', father_name, ' ', last_name)"), 'LIKE', "%{$fullName}%");
        })->with('user')->paginate($perPage);
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
        $staff=Staff::findOrFail($id);
        $user = $staff->user;
        $newStatus = ($user->account_status === 'enabled') ? 'disabled' : 'enabled';
        
         $user->update(['account_status' => $newStatus]);
         return $newStatus;
          
    }

    public function deleteStaff(int $id): void
    {
        DB::transaction(function () use ($id) {
            $staff=Staff::findOrFail($id);
            $staff->user()->delete(); 
            $staff->delete();
        });
    }


}


