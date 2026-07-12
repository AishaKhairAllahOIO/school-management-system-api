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
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\FinancialContractController;


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
        Route::get('/staff-announcements', [UserAnnouncementController::class, 'announcementsForStaff']);
        Route::get('/announcements/unread-count', [UserAnnouncementController::class, 'getUnreadCount']);
        Route::post('/announcements/mark-all-read', [UserAnnouncementController::class, 'markAllAsRead']);
        Route::get('/alerts', [UserAlertController::class, 'getStaffAlerts']);
        Route::get('/payment-alerts', [UserAlertController::class, 'getStaffPaymentAlerts']);
        Route::post('/alerts/mark-all-read', [UserAlertController::class, 'markAllAlertsRead']);
        Route::get('/alerts/unread-count', [UserAlertController::class, 'unreadAlertsCount']);
        Route::post('/personal-image', [UserController::class, 'uploadImage']);
        Route::get('/personal-image-url', [UserController::class, 'myPersonalPhotoUrl']);
        Route::get('/documents/{filename}', [DocumentController::class, 'showPersonalPhoto'])->where('filename', '.*');

        Route::middleware('role:teacher')->prefix('/teacher')->group(function () {
            Route::get('/show-profile', [UserController::class, 'teacherProfile']);
            Route::post('/teacher-alerts', [UserAlertController::class, 'teacherCreateAlerts']);
        });

        Route::middleware('role:counselor')->prefix('/counselor')->group(function () {
            Route::get('/show-profile', [UserController::class, 'counselorProfile']);
        });

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
    Route::get('/grades', [GradeAndClassroomController::class, 'indexGrades']);
    Route::get('/configurations', [GradeAndClassroomController::class, 'indexConfigurations']);
    Route::get('/classrooms', [GradeAndClassroomController::class, 'indexClassrooms']);
    Route::put('/', [AcademicSettingsController::class, 'update']);

    Route::post('/years', [AcademicSettingsController::class, 'storeYear']);
    Route::put('/years/{year}', [AcademicSettingsController::class, 'updateYear']);

    Route::post('/terms', [AcademicSettingsController::class, 'storeTerm']);
    Route::put('/terms/{term}', [AcademicSettingsController::class, 'updateTerm']);

    Route::post('/stages', [AcademicSettingsController::class, 'storeStage']);
    Route::post('/stages/{stage}', [AcademicSettingsController::class, 'updateStage']);


    Route::post('/grades', [GradeAndClassroomController::class, 'storeGrade']);
    Route::post('/grades/{grade}', [GradeAndClassroomController::class, 'updateGrade']);
    // Grade Configurations
    Route::post('/configurations', [GradeAndClassroomController::class, 'storeConfiguration']);
    Route::post('/configurations/{config}', [GradeAndClassroomController::class, 'updateConfiguration']);
    // Classrooms
    Route::post('/classrooms', [GradeAndClassroomController::class, 'storeClassroom']);
    Route::post('/classrooms/{classroom}', [GradeAndClassroomController::class, 'updateClassroom']);


    // جلب القوائم (الأعوام، الفصول، المراحل)
    Route::get('/years', [AcademicSettingsController::class, 'getYears']);
    Route::get('/terms', [AcademicSettingsController::class, 'getTerms']);
    Route::get('/stages', [AcademicSettingsController::class, 'getStages']);

    // جلب عناصر محددة بالـ ID (الصفوف، التكوينات، الشعب)
    Route::get('/grades/{id}', [GradeAndClassroomController::class, 'showGrade']);
    Route::get('/configurations/{id}', [GradeAndClassroomController::class, 'showConfiguration']);
    Route::get('/classrooms/{id}', [GradeAndClassroomController::class, 'showClassroom']);

    // --- دوال الحذف (DELETE) ---
    Route::delete('/years/{id}', [AcademicSettingsController::class, 'destroyYear']);
    Route::delete('/terms/{id}', [AcademicSettingsController::class, 'destroyTerm']);
    Route::delete('/stages/{id}', [AcademicSettingsController::class, 'destroyStage']);

    Route::delete('/grades/{id}', [GradeAndClassroomController::class, 'destroyGrade']);
    Route::delete('/configurations/{id}', [GradeAndClassroomController::class, 'destroyConfiguration']);
    Route::delete('/classrooms/{id}', [GradeAndClassroomController::class, 'destroyClassroom']);

    Route::get('/years/{id}', [AcademicSettingsController::class, 'showYear']);
    Route::get('/terms/{id}', [AcademicSettingsController::class, 'showTerm']);
    Route::get('/stages/{id}', [AcademicSettingsController::class, 'showStage']);
    Route::delete('/', [AcademicSettingsController::class, 'destroy']);



});

Route::prefix('admin/settings/general')->middleware('auth:sanctum')->group(function () {

    Route::get('/', [SchoolSettingsController::class, 'show']);
    Route::post('/', [SchoolSettingsController::class, 'update']);
    Route::get('/images', [SchoolSettingsController::class, 'indexImages']);
    Route::get('/images/{image}', [SchoolSettingsController::class, 'showImage']);


    Route::post('/images', [SchoolSettingsController::class, 'storeImages']);
    Route::post('/images/{image}', [SchoolSettingsController::class, 'updateImage']);
    Route::delete('/images/{image}', [SchoolSettingsController::class, 'destroyImage']);
    Route::delete('/', [SchoolSettingsController::class, 'destroy']);

});
Route::prefix('admin/finance/settings')->middleware('auth:sanctum')->group(function () {

    // سياسات التقسيط
    Route::get('/policies', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'getPolicies']);
    Route::post('/policies', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'storePolicy']);

    // خطط الرسوم الموحدة
    Route::get('/fee-plans', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'getFeePlans']);
    Route::post('/fee-plans', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'storeFeePlan']);
    Route::post('/policies/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'updatePolicy']);
    Route::delete('/policies/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'destroyPolicy']);

    // تعديل وحذف خطط الرسوم الموحدة
    Route::post('/fee-plans/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'updateFeePlan']);
    Route::delete('/fee-plans/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'destroyFeePlan']);
    Route::post('/policy-items/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'updatePolicyItem']);
    Route::delete('/policy-items/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'destroyPolicyItem']);

    Route::post('/extra-services/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'updateExtraService']);
    Route::delete('/extra-services/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'destroyExtraService']);
    Route::get('/policies/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'showPolicy']);
    Route::get('/fee-plans/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'showFeePlan']);
    Route::get('/policy-items/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'showPolicyItem']);
    Route::get('/extra-services/{id}', [\App\Http\Controllers\Finance\FinancialSettingsController::class, 'showExtraService']);
});


Route::prefix('admin/finance/contracts')->middleware('auth:sanctum')->group(function () {

    Route::get('/accounts', [FinancialContractController::class, 'index']);
    Route::get('/accounts/{studentId}', [FinancialContractController::class, 'show']);

    // 2️⃣ مسارات الأقساط (Installments)
    Route::get('/installments', [FinancialContractController::class, 'installmentsIndex']);
    Route::get('/installments/{id}', [FinancialContractController::class, 'showInstallment']);

    // 3️⃣ مسارات الصندوق والدفع (Payments)
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments', [PaymentController::class, 'store']);

    Route::post('/finalize', [FinancialContractController::class, 'finalize']);
    Route::post('/{accountId}', [FinancialContractController::class, 'update']);
    Route::post('/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);
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
Route::middleware('auth:sanctum')->prefix('admin/student')->group(function () {

    Route::post('/register', [StudentController::class, 'store']);
    Route::post('/import', [StudentController::class, 'importExcel']);
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


Route::post('/announcements',   [UserAnnouncementController::class, 'store']);
Route::delete('/announcements/{id}', [UserAnnouncementController::class, 'destroy']);

Route::post('/alerts', [UserAlertController::class, 'store']);

Route::delete('/alerts/{id}', [UserAlertController::class, 'destroy']);

Route::delete('/activity/{id}', [ActivityController::class, 'destroy']);
Route::post('/activity', [ActivityController::class, 'store']);

Route::post('/advisor-alerts', [UserAlertController::class, 'advisorCreateAlerts']);
Route::post('/staff-alerts', [UserAlertController::class, 'staffAlerts']);
Route::post('/payment-alerts', [UserAlertController::class, 'paymentAlerts']);


/// /////////////////////////////////////Mobile/////////////////////////////////////// ///



Route::prefix('user')->group(function () {
    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [UserAuthController::class, 'resendOtp']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::get('get-user-data', [UserController::class, 'getUserInfo']);
        Route::get('/my-activities', [ActivityController::class, 'show']);
        Route::get('/child-activities', [ActivityController::class, 'guardianViewActivities']);
        Route::get('/activity-unread-count',[ActivityController::class,'getUnreadCount']);
        Route::post('/activity-mark-all-read',[ActivityController::class,'markAllAsRead']);
        Route::post('/personal-image', [UserController::class, 'uploadImage']);
        Route::get('/personal-image-url', [UserController::class, 'myPersonalPhotoUrl']);
        Route::get('/photos/{filename}', [DocumentController::class, 'showPersonalPhoto'])->where('filename', '.*');
        Route::get('/child-alerts/{id}', [UserAlertController::class, 'childAlerts']);
        Route::get('/payment-alerts/{id}', [UserAlertController::class, 'childPaymentAlerts']);
        Route::get('/my-alerts', [UserAlertController::class, 'myAlerts']);
        Route::get('/my-announcements', [UserAnnouncementController::class, 'studentAnnouncements']);
        Route::get('/child-announcements', [UserAnnouncementController::class, 'guardianAnnouncements']);
        Route::get('/announcements/unread-count', [UserAnnouncementController::class, 'getUnreadCount']);
        Route::post('/announcements/mark-all-read', [UserAnnouncementController::class, 'markAllAsRead']);
        Route::post('/alerts/mark-all-read', [UserAlertController::class, 'markAllAlertsRead']);
        Route::get('/alerts/unread-count', [UserAlertController::class, 'unreadAlertsCount']);


        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::post('logout', [UserAuthController::class, 'logout']);
    });
});
