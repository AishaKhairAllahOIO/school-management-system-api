<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\TeacherProfileResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;
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

        if ($user->hasRole('student')) {

            $result = $this->userService->getStudent($user);

        } elseif ($user->hasRole('guardian')) {

            $result = $this->userService->getGuardian($user);

        } else {

            return $this->errorResponse('Unsupported user role.', 403);
        }

        return $this->successResponse(
            $result,
            'User information retrieved successfully.',
            200
        );
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

        return $this->successResponse(new TeacherProfileResource($user), 'Teacher profile retrieved successfully.', 200);
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

        return $this->successResponse(new CounselorProfileResource($user), 'Counselor profile retrieved successfully.', 200);
    }

    public function myPersonalPhotoUrl(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse([
            'photo_url' => $this->getPhotoUrl($user),
        ], 'Personal photo URL retrieved successfully.', 200);
    }

    private function getPhotoUrl(User $user): ?string
    {
        if (!$user->photo_url) {
            return null;
        }

        $photo = trim($user->photo_url);

        if (str_starts_with($photo, 'http')) {
            return $photo;
        }

        $photo = preg_replace(
            '/^.*?(users\/|defaults\/)/',
            '$1',
            $photo
        );

        return url('/api/documents/photos/' . ltrim($photo, '/'));
    }

    public function childPersonalPhotoUrl(Request $request, int $studentId)
    {
        $user = $request->user();

        if (!$user->hasRole('guardian') || !$user->guardian) {
            return $this->errorResponse('You are not authorized to access this resource.', 403);
        }

        $student = $user->guardian->students()->find($studentId);

        if (!$student) {
            return $this->errorResponse('The student does not exist or does not belong to you.', 404);
        }

        $studentUser = $student->user;

        if (!$studentUser || !$studentUser->photo_url) {
            return $this->successResponse([
                'photo_url' => null,
            ], 'The student does not have a photo in the system.', 200);
        }

        $photo = trim($studentUser->photo_url);

        $photo = preg_replace(
            '/^.*?(users\/|defaults\/)/',
            '$1',
            $photo
        );

        $photoUrl = url('/api/documents/photos/' . ltrim($photo, '/'));
        return $this->successResponse([
            'photo_url' => $photoUrl,
        ], 'Child\'s photo URL retrieved successfully.', 200);
    }
}
