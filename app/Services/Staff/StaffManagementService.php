<?php
namespace App\Services\Staff;

use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffManagementService
{
 
    private function formatUserPhotoUrls($paginator)
    {
        $paginator->getCollection()->transform(function ($item) {
            if ($item->user && $item->user->photo_url) {
                $item->user->photo_url = url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $item->user->photo_url), '/'));
            }
            return $item;
        });

        return $paginator;
    }

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


    public function getStaffByRole(string $roleName, int $perPage = 15)
    {
        if ($roleName === 'student') {
            $paginator = Student::whereHas('user', function ($query) use ($roleName) {
                    $query->role($roleName);
                })
                ->with([
                    'user.roles',
                    'enrollments' => function ($query) {
                        $query->withTrashed();
                    }
                ])
                ->paginate($perPage);

            return $this->formatUserPhotoUrls($paginator);
        }

        $paginator = Staff::withTrashed()
            ->whereHas('user', function ($query) use ($roleName) {
                $query->role($roleName);
            })
            ->with(['user.roles'])
            ->paginate($perPage);

        return $this->formatUserPhotoUrls($paginator);
    }


    public function ownProfile()
    {
        $user = auth()->user()->staff;
        return $user->load('user.roles');
    }

    public function getStaffProfile(int $id): Staff
    {
        return Staff::withTrashed()->with(['user.roles'])->findOrFail($id);
    }

    public function getAllStaff($perPage = 15)
    {
        $paginator = Staff::withTrashed()->with('user')->paginate($perPage);
        return $this->formatUserPhotoUrls($paginator);
    }

    public function getStaffById(int $id)
    {
        $staff = Staff::withTrashed()->findOrFail($id);
        return $staff;
    }

    public function updatePersonalData(int $id, array $data): Staff
    {
        return DB::transaction(function () use ($id, $data) {
            $staff = Staff::with('user')->findOrFail($id);

            if ($staff->trashed()) {
                throw new Exception('Cannot update data for a deleted staff member.', 422);
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

            $userData = array_intersect_key($data, array_flip($userFields));
            if (!empty($userData)) {
                $user->update($userData);
            }

            $staffData = array_intersect_key($data, array_flip($staffFields));
            if (!empty($staffData)) {
                $staff->update($staffData);
            }

            return $staff->refresh()->load('user');
        });
    }

    public function searchStaffByRoleAndName(string $roleName, string $fullName, int $perPage = 15)
    {
        $paginator = Staff::withTrashed()
            ->whereHas('user', function ($query) use ($roleName, $fullName) {
                $query->role($roleName);

                if (!empty($fullName)) {
                    $query->where(DB::raw("CONCAT(first_name, ' ', father_name, ' ', last_name)"), 'LIKE', "%{$fullName}%");
                }
            })
            ->with(['user.roles'])
            ->paginate($perPage);

        return $this->formatUserPhotoUrls($paginator);
    }

    public function getAllStaffAlphabetically(string $direction = 'asc', $perPage = 15)
    {
        $paginator = Staff::withTrashed()->join('users', 'staff.user_id', '=', 'users.id')
            ->select('staff.*')
            ->orderBy('users.first_name', $direction)
            ->orderBy('users.father_name', $direction)
            ->orderBy('users.last_name', $direction)
            ->with('user')
            ->paginate($perPage);

        return $this->formatUserPhotoUrls($paginator);
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
            $staff = Staff::withTrashed()->with('user')->findOrFail($staffId);

            if (!$staff->trashed()) {
                throw new Exception('This staff member is not deleted, so it cannot be restored.', 422);
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
