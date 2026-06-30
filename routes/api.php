<?php

use App\Http\Controllers\Admin\Student\StudentController;
use App\Http\Controllers\Auth\SystemAccessController;
use App\Http\Controllers\Setting\SchoolSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Notification\DeviceTokenController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Setting\AcademicSettingsController;
use App\Http\Controllers\RoleAndPermission\RoleController;
use App\Http\Controllers\User\UserAlertController;
use App\Http\Controllers\User\UserAnnouncementController;
use App\Http\Controllers\web\ActivityController;


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
    // Route::post('grade-levels/structure',[AcademicSettingsController::class,])


    Route::middleware('auth:sanctum')->group(function () {
            Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
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
            Route::post('/create-activity', [ActivityController::class, 'store']);
            Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
            Route::delete('/logout', [SystemAccessController::class, 'logout']);

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
Route::middleware('auth:sanctum', 'role:super_admin')->prefix('data')->group(function () {
    Route::get('/super_admin', [UserController::class, 'myProfile']);
    Route::put('/super_admin', [UserController::class, 'updateMyAdminProfile']);
});
Route::middleware('auth:sanctum')->prefix('admin')->group(function(){
   Route::post('/student/register',[StudentController::class,'store']);
   Route::post('/student/import',[StudentController::class,'importExcel']);
   Route::get('/student/import-batches/{batch}/errors/export', [StudentController::class, 'exportErrors'])
     ->middleware('can:student:create');
     Route::get('/student/import-batches/{batch}/status', [StudentController::class, 'getImportStatus'])
     ->middleware('can:student:create');
     Route::get('/student/import-batches/history', [StudentController::class, 'getBatchesHistory']);
});



Route::post('/announcements',     [UserAnnouncementController::class, 'store']);
