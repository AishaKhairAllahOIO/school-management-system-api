<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePersonalImageRequest;
use App\Http\Resources\Auth\ProfileResource;
use App\Http\Resources\User\TeacherProfileResource;
use App\Models\User;
use Exception;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\UpdateStaffRequest;
use App\Http\Resources\Auth\AcademicProfileResource;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\User\CounselorProfileResource;

class UserController extends Controller
{
    use ApiResource;
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
        public function roleCounts()
    {
        try {
            $counts = $this->userService->getRoleCounts();
            return $this->successResponse($counts, 'تم جلب إحصائيات الأدوار بنجاح.');
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب الإحصائيات.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * جلب قائمة المستخدمين بناءً على دور محدد
     */
    public function getByRole(string $role, Request $request)
    {
        try {
            $perPage = $request->query('per_page', 15);
            $users = $this->userService->getUsersByRole($role, $perPage);
            
            return $this->successResponse(UserResource::collection($users), "تم جلب مستخدمي دور الـ {$role} بنجاح.");
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب المستخدمين.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getUserInfo(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->hasRole('student'))
            $result = $this->userService->getStudent($user);

        else if ($user->hasRole('guardian'))
            $result = $this->userService->getGuardian($user);

        return $this->successResponse($result, 'تم جلب بيانات لوحةالتحكم بنجاح', 200);
    }

    public function teacherProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if (!$user->hasRole('teacher')) {
            return $this->errorResponse('User is not a teacher.', 403);
        }

        return $this->successResponse(new TeacherProfileResource($user), 'تم جلب بيانات المستخدم بنجاح', 200);
    }
    public function counselorProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if (!$user->hasRole('counselor')) {
            return $this->errorResponse('User is not a counselor.', 403);
        }

        return $this->successResponse(new CounselorProfileResource($user), 'تم جلب بيانات المستخدم بنجاح', 200);
    }
    public function uploadImage(UpdatePersonalImageRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }
        $updatedUser = $this->userService->updateProfileImage($user, $request->file('personal_image'));

        return $this->successResponse([
            'photo_url' => $this->getPhotoUrl($updatedUser),
        ], 'تم تعيين الصورة الشخصية بنجاح', 200);
    }

    public function myPersonalPhotoUrl(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse([
            'photo_url' => $this->getPhotoUrl($user),
        ], 'تم جلب رابط الصورة الشخصية بنجاح', 200);
    }

    private function getPhotoUrl(User $user): ?string
    {
        if (!$user->personal_photo) {
            return null;
        }

        if ($user->hasRole('student') || $user->hasRole('guardian')) {
            return url('/api/user/photos/' . ltrim($user->personal_photo, '/'));
        }

        return url('/api/auth/documents/' . ltrim($user->personal_photo, '/'));
    }
}
