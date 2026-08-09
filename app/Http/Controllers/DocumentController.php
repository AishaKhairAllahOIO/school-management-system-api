<?php

namespace App\Http\Controllers;

use App\ApiResource;
use App\Models\GradeConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use ApiResource;


public function showPhoto($path)
    {
        $userAuth = Auth::guard('sanctum')->user();
        if (!$userAuth) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $safePath = urldecode($path);
        if (str_contains($safePath, 'http://') || str_contains($safePath, 'https://')) {
            $safePath = parse_url($safePath, PHP_URL_PATH);
        }
        $safePath = preg_replace('/^.*?(users\/|defaults\/|documents\/|guardians\/|staff\/|students\/)/', '$1', $safePath);
        $safePath = ltrim($safePath, '/');

        if ($safePath === '' || str_contains($safePath, '..')) {
            return $this->errorResponse('Invalid photo path', 422);
        }

        if (!$this->userCanViewPhoto($userAuth, $safePath)) {
            return $this->errorResponse('غير مصرح لك بعرض هذه الصورة.', 403);
        }

        $disk = null;
        if (Storage::disk('local')->exists($safePath)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($safePath)) {
            $disk = 'public';
        }

        if (!$disk) {
            return $this->errorResponse('Photo not found.', 404);
        }

        return response()->file(Storage::disk($disk)->path($safePath));
    }


    private function userCanViewPhoto($userAuth, string $path): bool
    {
        if (str_starts_with($path, 'defaults/')) {
            return true;
        }

        if ($userAuth->photo_url === $path) {
            return true;
        }

        if ($userAuth->hasAnyRole(['super_admin', 'secretary', 'counselor','teacher'])) {
            return true;
        }

        $targetUser = User::where('photo_url', $path)->first();
        if (!$targetUser) {
            return false;
        }

        if ($targetUser->hasRole('student') && $targetUser->student) {

            if ($userAuth->hasRole('guardian') && $userAuth->guardian) {
                return (int) $targetUser->student->guardian_id === (int) $userAuth->guardian->id;
            }

            $activeEnrollment = $targetUser->student->enrollments()
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->latest()
                ->first();

            if (!$activeEnrollment) {
                return false;
            }

            if ($userAuth->hasRole('teacher') && $userAuth->staff) {
                $teacherClassRooms = $userAuth->staff->teacherAssignments()
                    ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                    ->pluck('class_room_id')
                    ->toArray();

                return in_array($activeEnrollment->class_room_id, $teacherClassRooms);
            }

            if ($userAuth->hasRole('adviser')) {
                return GradeConfiguration::where('supervisor_id', $userAuth->id)
                    ->where('grade_level_id', $activeEnrollment->grade_level_id)
                    ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                    ->exists();
            }
        }

        return false;
    }
}
