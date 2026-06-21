<?php

use App\Http\Controllers\Auth\SystemAccessController;
use App\Http\Controllers\Setting\SchoolSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Setting\AcademicSettingsController;
<<<<<<< HEAD
use App\Http\Controllers\RoleAndPermission\RoleController;
=======
use App\Http\Controllers\web\ActivityController;
>>>>>>> 36d654bf7416143ddfda67f0b4731ad830c31d16

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('auth')->group(function () {

    Route::post('/login', [SystemAccessController::class, 'loginWeb']);
    Route::post('/verify-otp', [SystemAccessController::class, 'verifyOtpWeb']);
    Route::post('/loginMobile', [SystemAccessController::class, 'loginMobile']);
    Route::post('/verify-otp-mobile', [SystemAccessController::class, 'vertifyOtpMobile']);
    Route::post('/password/forgot', [SystemAccessController::class, 'forgotPassword']);
    Route::post('/password/verify-otp', [SystemAccessController::class, 'verifyPassword']);
    Route::post('password/resend-otp', [SystemAccessController::class, 'forgotPassword']);
    Route::post('/password/reset', [SystemAccessController::class, 'resetPassword']);
    Route::post('/create-activity', [ActivityController::class, 'store']);
    // Route::post('grade-levels/structure',[AcademicSettingsController::class,])


    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('general', [SchoolSettingsController::class, 'show']);
            Route::put('general', [SchoolSettingsController::class, 'update']);
            Route::get('academic', [AcademicSettingsController::class, 'show']);
            Route::put('academic', [AcademicSettingsController::class, 'update']);

            Route::post('/grade-levels/structure', [AcademicSettingsController::class, 'createStracture']);

            Route::post('/one-grade-level', [AcademicSettingsController::class, 'createStracture']);


            Route::get('/grade-levels',            [AcademicSettingsController::class, 'showAllGrades']);
            Route::get('/grade-levels/{id}',       [AcademicSettingsController::class, 'showOneGrade']);
            Route::put('/grade-levels/{id}',       [AcademicSettingsController::class, 'updateGrade']);
            Route::delete('/grade-levels/{id}',    [AcademicSettingsController::class, 'destroyGrade']);

            Route::put('/classrooms/{id}',         [AcademicSettingsController::class, 'updateClassroom']);
            Route::delete('/classrooms/{id}',      [AcademicSettingsController::class, 'destroyClassroom']);
        });
        Route::delete('/logout', [SystemAccessController::class, 'logout']);
    });
});


Route::prefix('user')->group(function () {

    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [UserAuthController::class, 'resendOtp']);
    Route::post('forgot-password', [UserAuthController::class, 'resendOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('logout', [UserAuthController::class, 'logout']);
        Route::get('/photos/{filename}', [DocumentController::class, 'showPersonalPhoto']);
        Route::get('/get-user-data', [UserController::class, 'getUserInfo']);
        Route::get('/activites', [ActivityController::class, 'show']);
        Route::get('/children/activities', [ActivityController::class, 'guardianViewActivities']);
    });
});

Route::middleware('auth:sanctum')->prefix('settings')->group(function () {

    Route::get('general', [SchoolSettingsController::class, 'show']);
    Route::put('general', [SchoolSettingsController::class, 'update']);
    Route::get('academic', [AcademicSettingsController::class, 'show']);
    Route::put('academic', [AcademicSettingsController::class, 'update']);


});

Route::middleware('auth:sanctum', 'role:super_admin')->prefix('role')->group(function () {
    Route::get('/systemRoles', [RoleController::class, 'index']);
    Route::get('/systemModules', [RoleController::class, 'getSystemModules']);
    Route::put('/{id}/permissions', [RoleController::class, 'sync']);
});



