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
use App\Http\Controllers\Setting\GradeAndClassroomController;


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
        Route::get('/announcements', [UserAnnouncementController::class, 'announcementsForStaff']);
        Route::get('/alerts', [UserAlertController::class, 'getStaffAlerts']);


        Route::prefix('admin/settings')->group(function () {
    // Route::get('/', [AcademicSettingsController::class, 'index']);
    // Route::put('/', [AcademicSettingsController::class, 'update']);
    
    // Route::post('/years', [AcademicSettingsController::class, 'storeYear']);
    // Route::put('/years/{year}', [AcademicSettingsController::class, 'updateYear']);
    
    // Route::post('/terms', [AcademicSettingsController::class, 'storeTerm']);
    // Route::put('/terms/{term}', [AcademicSettingsController::class, 'updateTerm']);

    // Route::post('/stages', [AcademicSettingsController::class, 'storeStage']);
    // Route::put('/stages/{stage}', [AcademicSettingsController::class, 'updateStage']);
    // Route::patch('/stages/{stage}', [AcademicSettingsController::class, 'updateStage']);

    //         Route::post('/grade-levels/structure', [AcademicSettingsController::class, 'createStracture']);

    //         Route::post('/one-grade-level', [AcademicSettingsController::class, 'createStracture']);



    //         Route::get('/grade-levels',            [AcademicSettingsController::class, 'showAllGrades']);
    //         Route::get('/grade-levels/{id}',       [AcademicSettingsController::class, 'showOneGrade']);
    //         Route::put('/grade-levels/{id}',       [AcademicSettingsController::class, 'updateGrade']);
    //         Route::delete('/grade-levels/{id}',    [AcademicSettingsController::class, 'destroyGrade']);

    //         Route::put('/classrooms/{id}',         [AcademicSettingsController::class, 'updateClassroom']);
    //         Route::delete('/classrooms/{id}',      [AcademicSettingsController::class, 'destroyClassroom']);
        });
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::delete('/logout', [SystemAccessController::class, 'logout']);
    });
});
//////////////////////////////////////////////////////////
Route::middleware('auth:sanctum')->prefix('admin/settings')->group(function () {


    Route::get('/', [AcademicSettingsController::class, 'index']);
    Route::put('/', [AcademicSettingsController::class, 'update']);
    
    Route::post('/years', [AcademicSettingsController::class, 'storeYear']);
    Route::put('/years/{year}', [AcademicSettingsController::class, 'updateYear']);
    
    Route::post('/terms', [AcademicSettingsController::class, 'storeTerm']);
    Route::put('/terms/{term}', [AcademicSettingsController::class, 'updateTerm']);

    Route::post('/stages', [AcademicSettingsController::class, 'storeStage']);
    Route::put('/stages/{stage}', [AcademicSettingsController::class, 'updateStage']);


    Route::post('/grades', [GradeAndClassroomController::class, 'storeGrade']);
    Route::post('/grades/{grade}', [GradeAndClassroomController::class, 'updateGrade']);
            // Grade Configurations
    Route::post('/configurations', [GradeAndClassroomController::class, 'storeConfiguration']);
    Route::post('/configurations/{config}', [GradeAndClassroomController::class, 'updateConfiguration']);
            // Classrooms
    Route::post('/classrooms', [GradeAndClassroomController::class, 'storeClassroom']);
    Route::post('/classrooms/{classroom}', [GradeAndClassroomController::class, 'updateClassroom']);
});

Route::prefix('admin/settings/general')->middleware('auth:sanctum')->group(function () {
    
    Route::get('/', [SchoolSettingsController::class, 'show']);
    Route::put('/', [SchoolSettingsController::class, 'update']);
    
    Route::post('/images', [SchoolSettingsController::class, 'storeImages']);
    Route::post('/images/{image}', [SchoolSettingsController::class, 'updateImage']);
    Route::delete('/images/{image}', [SchoolSettingsController::class, 'destroyImage']);
    
});

//////////////////////////////////////////////////
Route::middleware('auth:sanctum', 'role:super_admin')->prefix('role')->group(function () {
    Route::get('/systemRoles', [RoleController::class, 'index']);
    Route::get('/systemModules', [RoleController::class, 'getSystemModules']);
    Route::put('/{id}/permissions', [RoleController::class, 'sync']);
});
//////////////////////////////////////////////////
Route::middleware('auth:sanctum', 'role:super_admin')->prefix('data')->group(function () {
    Route::get('/super_admin', [UserController::class, 'myProfile']);
    Route::put('/super_admin', [UserController::class, 'updateMyAdminProfile']);
});
///////////////////////////////////////////////////////
Route::middleware('auth:sanctum')->prefix('admin/student')->group(function(){

   Route::post('/register',[StudentController::class,'store']);
   Route::post('/import',[StudentController::class,'importExcel']);
   Route::get('/import-batches/{batch}/errors/export', [StudentController::class, 'exportErrors'])
     ->middleware('can:student:create');
     Route::get('/import-batches/{batch}/status', [StudentController::class, 'getImportStatus'])
     ->middleware('can:student:create');
     Route::get('/import-batches/history', [StudentController::class, 'getBatchesHistory']);
});

///////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum'])->prefix('admin/students')->group(function () {

    Route::get('/', [StudentController::class, 'index'])
        ->middleware('can:student:view_profile'); // أو can:student:search
    Route::get('/{id}', [StudentController::class, 'show'])
        ->middleware('can:student:view_profile');
    Route::get('/{enrollmentId}/full-profile', [StudentController::class, 'showFullProfile'])
        ->middleware('can:student:view_profile');    
    Route::post('/{student}/personal', [StudentController::class, 'updatePersonal'])
        ->middleware('can:student:edit');
    Route::post('/enrollments/{enrollment}', [StudentController::class, 'updateEnrollment']);
         // أو أي صلاحية تراها مناسبة لتعديل القيود
    Route::post('/guardians/{guardian}/personal', [StudentController::class, 'updateGuardian'])
        ->middleware('can:student:edit');
    Route::delete('/{id}', [StudentController::class, 'destroy'])
        ->middleware('can:student:delete');
    Route::post('/{enrollmentId}/toggle-account-status', [StudentController::class, 'toggleAccountStatus'])
        ->middleware('can:account:toggle_status');    
});


/// ////////////////////////////////////////////////////////////////////////////////// ///


Route::post('/announcement',   [UserAnnouncementController::class, 'store']);
Route::delete('/announcement/{id}', [UserAnnouncementController::class, 'destroy']);

Route::post('/alerts', [UserAlertController::class, 'store']);

Route::delete('/alerts/{id}', [UserAlertController::class, 'destroy']);

Route::post('/activity', [ActivityController::class, 'store']);
Route::get('/activites', [ActivityController::class, 'show']);
Route::delete('/activity',[ActivityController::class,'destroy']);




/// /////////////////////////////////////Mobile/////////////////////////////////////// ///



Route::prefix('user')->group(function () {
    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [UserAuthController::class, 'resendOtp']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::get('get-user-data', [UserController::class, 'getUserInfo']);
        Route::get('/photos/{filename}', [DocumentController::class, 'showPersonalPhoto']);
        Route::get('/alerts/{id}', [UserAlertController::class, 'childAlerts']);
        Route::get('/payment-alerts/{id}', [UserAlertController::class, 'childPaymentAlerts']);
        Route::get('/alerts', [UserAlertController::class, 'myAlerts']);
        Route::get('/announcements', [UserAnnouncementController::class, 'announcementsForStudent']);

        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::post('logout', [UserAuthController::class, 'logout']);
    });
});
