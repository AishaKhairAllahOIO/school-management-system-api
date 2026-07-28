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

        return $this->successResponse($result, 'تم جلب بيانات لوحة التحكم بنجاح', 200);
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

    public function myPersonalPhotoUrl(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse([
            'photo_url' => $this->getPhotoUrl($user),
        ], 'تم جلب رابط الصورة بنجاح', 200);
    }

    // 🚀 التعديل الجوهري: تبسيط الدالة وتوجيهها لـ photo_url والمسار العالمي المحمي
    private function getPhotoUrl(User $user): ?string
    {
        if (!$user->photo_url) {
            return null;
        }

        return url('/api/documents/photos/' . ltrim($user->photo_url, '/'));
    }

    public function childPersonalPhotoUrl(Request $request, int $studentId)
    {
        $user = $request->user();

        if (!$user->hasRole('guardian') || !$user->guardian) {
            return $this->errorResponse('غير مصرح لك بالوصول. هذا المسار مخصص لأولياء الأمور.', 403);
        }

        $student = $user->guardian->students()->find($studentId);

        if (!$student) {
            return $this->errorResponse('الطالب غير موجود أو لا يتبع لك.', 404);
        }

        $studentUser = $student->user;

        // 🚀 الاعتماد على photo_url بدلاً من personal_photo
        if (!$studentUser || !$studentUser->photo_url) {
            return $this->successResponse([
                'photo_url' => null,
            ], 'الطالب لا يملك صورة في النظام.', 200);
        }

        $photoUrl = url('/api/documents/photos/' . ltrim($studentUser->photo_url, '/'));

        return $this->successResponse([
            'photo_url' => $photoUrl,
        ], 'تم جلب رابط صورة الابن بنجاح', 200);
    }
}
