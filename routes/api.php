<?php

use App\Http\Controllers\Auth\SystemAccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('auth')->group(function () {
    
    Route::post('/login', [SystemAccessController::class, 'login']);
    Route::post('/verify-otp', [SystemAccessController::class, 'verifyOtp']);
    Route::post('/password/forgot', [SystemAccessController::class, 'forgotPassword']);
    Route::post('/password/verify-otp', [SystemAccessController::class, 'verifyPassword']);
    Route::post('password/resend-otp', [SystemAccessController::class,'forgotPassword']);
    Route::post('/password/reset', [SystemAccessController::class, 'resetPassword']);
    Route::middleware('auth:sanctum')->delete('/logout', [SystemAccessController::class, 'logout']);
});


