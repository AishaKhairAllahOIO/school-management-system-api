<?php

namespace App\Http\Controllers;

use App\ApiResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{

use ApiResource;
    public function showPersonalPhoto($filename)
    {
       $userAuth = Auth::guard('sanctum')->user();
        if(!$userAuth)
            return $this->errorResponse('Unauthenticated', 401);


       $safeFilename = ltrim($filename, '/');

        if ($safeFilename === '' || str_contains($safeFilename, '..')) {
            return $this->errorResponse('Unvalied photo name', 422);
        }

        if (!$this->userCanViewPhoto($userAuth, $safeFilename)) {
            return $this->errorResponse('Not allowed to view photo', 403);
        }

 if (!Storage::disk('local')->exists($safeFilename)) {
            return $this->errorResponse('File not found.', 404);
        }

        return response()->file(Storage::disk('local')->path($safeFilename));
       }


    private function userCanViewPhoto($userAuth, string $filename): bool
    {
        if ($userAuth->personal_photo === $filename || $userAuth->photo_url === $filename) {
            return true;
        }

        if ($userAuth->hasAnyRole(['super_admin', 'secretary', 'adviser', 'counselor', 'teacher'])) {
            return true;
        }

        $guardian = $userAuth->guardian;
        if ($guardian) {
            $belongsToChild = $guardian->students()
                ->whereHas('user', function ($q) use ($filename) {
                    $q->where('personal_photo', $filename)->orWhere('photo_url', $filename);
                })
                ->exists();

            if ($belongsToChild) {
                return true;
            }
        }

        return false;
    }
}
