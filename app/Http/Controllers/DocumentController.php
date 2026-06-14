<?php

namespace App\Http\Controllers;

use App\ApiResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{

use ApiResource;
    public function showPersonalPhoto($filename)
    {
       $userAuth=Auth::guard('sanctum')->user();
       abort_if(!$userAuth,$this->errorResponse('Unauthenticated',401));

        $path = storage_path('app/public/' . $filename);

        if (!File::exists($path)) {
            return $this->errorResponse('File not found.', 404);
        }

        return response()->file($path);
    }
}
