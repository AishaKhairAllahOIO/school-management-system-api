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
                    $disk = config('filesystems.public_disk');
                    if (Storage::disk($disk)->exists($user->photo_url)) {
                        Storage::disk($disk)->delete($user->photo_url);
                    }
                }
                $data['photo_url'] = $data['photo_url']
                    ->store('users/staff', config('filesystems.public_disk'));
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
    public function filterStaff(array $filters, int $perPage = 15)
    {
        // 1. التاريخ هو السيد (مع وضع تاريخ اليوم كقيمة افتراضية للحماية)
        $targetDate = $filters['attendance_date'] ?? now()->toDateString();

        $query = Staff::withTrashed()->with([
            'user.roles',
            // جلب سجل الحضور لهذا التاريخ فقط (إن وجد)
            'attendances' => function ($q) use ($targetDate) {
                $q->whereDate('attendance_date', $targetDate);
            }
        ]);

        // --- الفلاتر الأساسية الثابتة ---
        if (!empty($filters['role'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->role($filters['role']);
            });
        }

        if (!empty($filters['search'])) {
            $safeSearch = str_replace(['%', '_'], ['\%', '\_'], trim($filters['search']));
            $query->whereHas('user', function ($q) use ($safeSearch) {
                $q->where(DB::raw("CONCAT(first_name, ' ', father_name, ' ', last_name)"), 'LIKE', "%{$safeSearch}%")
                    ->orWhere('first_name', 'LIKE', "%{$safeSearch}%")
                    ->orWhere('last_name', 'LIKE', "%{$safeSearch}%");
            });
        }

        // --- 💡 فلتر حالة الحضور ونوع الغياب (تم إصلاح منطق الحاضر) ---
        if (!empty($filters['attendance_status'])) {
            $attendanceStatus = $filters['attendance_status'];

            if ($attendanceStatus === 'present') {
                $query->whereDoesntHave('attendances', function ($q) use ($targetDate) {
                    $q->whereDate('attendance_date', $targetDate);
                });
            } else {
                // للغياب والإجازات، نبحث في السجلات الموجودة
                $query->whereHas('attendances', function ($q) use ($targetDate, $attendanceStatus, $filters) {
                    $q->whereDate('attendance_date', $targetDate)
                        ->where('status', $attendanceStatus);

                    // الفلتر الفرعي لنوع الغياب
                    if (in_array($attendanceStatus, ['absent', 'partial_absence']) && !empty($filters['absence_type'])) {
                        $q->where('absence_type', $filters['absence_type']);
                    }
                });
            }
        }

        // الترتيب
        $direction = (isset($filters['sort']) && strtolower($filters['sort']) === 'desc') ? 'desc' : 'asc';
        $query->join('users', 'staff.user_id', '=', 'users.id')
            ->select('staff.*')
            ->orderBy('users.first_name', $direction)
            ->orderBy('users.father_name', $direction);

        $paginator = $query->paginate($perPage);

        // تطبيق تهيئة الصور أولاً
        $paginator = $this->formatUserPhotoUrls($paginator);

        // --- 💡 تشكيل الاستجابة (Transform) لتطابق متطلبات الفرونت إند تماماً ---
        $paginator->getCollection()->transform(function ($staff) use ($targetDate) {
            $attendanceRecord = $staff->attendances->first();

            // بناء كائن الحضور (سواء كان له سجل أم لا)
            $staff->setAttribute('attendance', [
                'id' => $attendanceRecord ? $attendanceRecord->id : null,
                'status' => $attendanceRecord ? $attendanceRecord->status : 'present', // الافتراضي حاضر
                'absence_type' => $attendanceRecord ? $attendanceRecord->absence_type : null,
                'staff_leave_id' => $attendanceRecord ? $attendanceRecord->staff_leave_id : null,
                'attendance_date' => $targetDate,
            ]);

            // إخفاء المصفوفة القديمة المربكة لكي يكون الـ JSON نظيفاً
            $staff->makeHidden('attendances');

            return $staff;
        });

        return $paginator;
    }
}
