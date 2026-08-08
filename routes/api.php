<?php

use App\Http\Controllers\Admin\BellController;
use App\Http\Controllers\Admin\Student\StudentController;
use App\Http\Controllers\Auth\SystemAccessController;
use App\Http\Controllers\Setting\AssessmentComponentController;
use App\Http\Controllers\Setting\GradeSubjectController;
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
use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Setting\GradeAndClassroomController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\FinancialContractController;
use App\Http\Controllers\Admin\Staff\StaffController;
use App\Http\Controllers\Admin\Student\ExpulsionController;
use App\Http\Controllers\Admin\SystemNoticeController;
use App\Http\Controllers\Finance\FinancialSettingsController;
use App\Http\Controllers\Setting\SubjectController;
use App\Http\Controllers\Student\PracticeQuizController as StudentPracticeQuizController;
use App\Http\Controllers\Student\StudentMarkDisplayController;
use App\Http\Controllers\Student\StudentMaterialController;
use App\Http\Controllers\Teacher\ClassStudentEvaluationController;
use App\Http\Controllers\Teacher\HomeworkController;
use App\Http\Controllers\Teacher\MarkController;
use App\Http\Controllers\Teacher\PracticeQuizController;
use App\Http\Controllers\Teacher\TeacherDropdownController;
use App\Http\Controllers\Teacher\TeacherMaterialController;
use App\Http\Controllers\User\SentAlertController;
use App\Http\Controllers\Web\SchoolLawController;
use App\Http\Controllers\Admin\Student\StudentAttendanceSettingController;
use App\Http\Controllers\Admin\Student\StudentAttendanceController;
use App\Http\Controllers\Scheduling\ScheduleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/documents/photos/{path}', [DocumentController::class, 'showPhoto'])
        ->where('path', '.*');
});




Route::prefix('auth')->group(function () {

    Route::post('/login', [SystemAccessController::class, 'loginWeb']);
    Route::post('/verify-otp', [SystemAccessController::class, 'verifyOtpWeb']);
    Route::post('/loginMobile', [SystemAccessController::class, 'loginMobile']);
    Route::post('/verify-otp-mobile', [SystemAccessController::class, 'vertifyOtpMobile']);
    Route::post('/password/forgot', [SystemAccessController::class, 'forgotPassword']);
    Route::post('/password/verify-otp', [SystemAccessController::class, 'verifyPassword']);
    Route::post('password/resend-otp', [SystemAccessController::class, 'forgotPassword']);
    Route::post('/password/reset', [SystemAccessController::class, 'resetPassword']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::get('/staff-announcements', [UserAnnouncementController::class, 'announcementsForStaff']);
        Route::get('/announcements/unread-count', [UserAnnouncementController::class, 'getUnreadCount']);
        Route::post('/announcements/mark-all-read', [UserAnnouncementController::class, 'markAllAsRead']);
        Route::get('/personal-image-url', [UserController::class, 'myPersonalPhotoUrl']);

        Route::prefix('alerts')->controller(UserAlertController::class)->group(function () {
            Route::get('/show/general/staff', 'getStaffAlerts');
            Route::get('/show/payments/staff', 'getStaffPaymentAlerts');
            Route::get('/unread-count', 'unreadAlertsCount');
            Route::post('/mark-all-read', 'markAllAlertsRead');
        });

        Route::prefix('bell')->controller(BellController::class)->group(function () {
            Route::get('/count/unread', 'getBellUnreadCount');
            Route::post('/mark/all/read', 'markAllBellItemsAsRead');
        });


        Route::prefix('/scheduale')->controller(ScheduleController::class)->group(function () {
        Route::post('/generate','generate');
        Route::post('/regenerate','regenerate');
        Route::put('/update','updateEntry');
        Route::get('/show/all/{scheduleId}','adminView');
        Route::get('/teacher/show','allTeachersWeekly');
        });



        Route::prefix('created/alerts')->controller(SentAlertController::class)->group(function () {
            Route::get('/show/by/role', 'index');
            Route::put('/update/{id}', 'update');
            Route::delete('/delete/group/{id}', 'destroy');
        });
        Route::delete('/delete/alert/{id}', [UserAlertController::class, 'destroy']);

        Route::middleware('role:secretary|super_admin')->group(function () {
            Route::prefix('alerts')->controller(UserAlertController::class)->group(function () {
                Route::post('general/staff/send', 'staffAlerts');
                Route::post('/payments/staff/send', 'paymentAlerts');
            });

            Route::prefix('system-notices')->controller(SystemNoticeController::class)->group(function () {
                Route::get('/show/alerts', 'index');
                Route::get('/unread-count', 'unreadCount');
                Route::post('/mark-all-read', 'markAllAsRead');
            });
            Route::post('/school/law/create', [SchoolLawController::class, 'store']);
            Route::get('/school/laws/all/show', [SchoolLawController::class, 'index']);
            Route::get('/school/law/one/show/{id}', [SchoolLawController::class, 'show']);
            Route::post('/school/law/update/{id}', [SchoolLawController::class, 'update']);
            Route::delete('/school/law/delete/{id}', [SchoolLawController::class, 'destroy']);

        });

        Route::middleware('role:adviser|super_admin')->group(function () {

            Route::prefix('alerts')->controller(UserAlertController::class)->group(function () {
                Route::post('/for-student/send', 'advisorCreateAlerts');
            });

            Route::prefix('activity')->controller(ActivityController::class)->group(function () {
                Route::get('/show/all', 'showAllActivity');
                Route::get('/show/one/{id}', 'showActivity');
                Route::post('/create', 'store');
                Route::delete('/delete/{id}', 'destroy');
                Route::post('/update/{id}', 'updateActivity');
            });
            Route::post('/announcements', [UserAnnouncementController::class, 'store']);
            Route::delete('/announcements/{id}', [UserAnnouncementController::class, 'destroy']);
            Route::post('/announcement/update/{id}', [UserAnnouncementController::class, 'update']);
            Route::get('creater/show/announcements', [UserAnnouncementController::class, 'adminAnnouncements']);
        });

        Route::middleware('role:teacher')->prefix('/teacher')->group(function () {
            Route::get('/show-profile', [UserController::class, 'teacherProfile']);
            Route::post('/teacher-alerts', [UserAlertController::class, 'teacherCreateAlerts']);
            Route::get('/subjects-tree', [TeacherDropdownController::class, 'subjectsTree']);
            Route::get('/classrooms/{classRoomId}/students', [TeacherDropdownController::class, 'classroomStudents']);
            Route::post('/create/homeworks', [HomeworkController::class, 'store']);
            Route::get('/show/all/homeworks', [HomeworkController::class, 'index']);
            Route::post('/update/homework/{id}', [HomeworkController::class, 'update']);
            Route::delete('/delete/homework/{id}', [HomeworkController::class, 'destroy']);
            Route::get('/show/one/homework/{id}', [HomeworkController::class, 'show']);
            Route::post('/create/evaluation', [ClassStudentEvaluationController::class, 'store']);
            Route::get('/show/all/evaluations', [ClassStudentEvaluationController::class, 'index']);
            Route::post('/update/evaluation/{id}', [ClassStudentEvaluationController::class, 'update']);
            Route::delete('/delete/evaluation/{id}', [ClassStudentEvaluationController::class, 'destroy']);
            Route::get('/show/one/evaluation/{id}', [ClassStudentEvaluationController::class, 'show']);
            Route::post('/gradebook/marks', [MarkController::class, 'storeMarks']);
            Route::get('/gradebook/subject/{gradeSubjectId}/classroom/{classRoomId}', [MarkController::class, 'getGradebook']);
            Route::prefix('practice-quizzes')->controller(PracticeQuizController::class)->group(function () {

                Route::post('/create/quiz', 'store');
                Route::get('/show/quiz/by/grade-subject/{gradeSubjectId}', 'getQuizzesByGradeSubject');
                Route::get('/show/one/quiz/{quizId}', 'show');
                Route::patch('/toggle-active/quiz/{id}', 'toggleActive');
                Route::delete('/delete/quiz/{id}', 'destroy');

            });

            Route::prefix('helper/materials')->controller(TeacherMaterialController::class)->group(function () {
                Route::post('/upload', 'store');
                Route::get('/show/by-subject/{gradeSubjectId}', 'index');
                Route::get('/show/one/{id}', 'show');
                Route::delete('/delete/{id}', 'destroy');
            });
        });

        Route::middleware('role:counselor')->prefix('/counselor')->group(function () {
            Route::get('/show-profile', [UserController::class, 'counselorProfile']);
        });

        Route::middleware('permission:account:toggle_status')->prefix('expulsions')->group(function () {

            Route::get('/pending', [ExpulsionController::class, 'getPending']);
            Route::post('/confirm', [ExpulsionController::class, 'confirm']);

        });
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::delete('/logout', [SystemAccessController::class, 'logout']);
    });
});

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
    Route::post('/configurations', [GradeAndClassroomController::class, 'storeConfiguration']);
    Route::post('/configurations/{config}', [GradeAndClassroomController::class, 'updateConfiguration']);
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

    // Route::get('/academic/statistics', [AcademicSettingsController::class, 'statistics']);
    // Route::get('/academic-stages/with-grades', [AcademicSettingsController::class, 'indexWithGrades']);
    // Route::get('/leave-types', [LeaveTypeController::class, 'index']);
    // Route::post('/leave-types', [LeaveTypeController::class, 'store']);
    // Route::get('/leave-types/{id}', [LeaveTypeController::class, 'show']);
    // Route::put('/leave-types/{id}', [LeaveTypeController::class, 'update']);
    // Route::delete('/leave-types/{id}', [LeaveTypeController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->prefix('subject/setting')->group(function () {
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/subject/store', [SubjectController::class, 'store']);
        Route::get('/subjects/show', [SubjectController::class, 'index']);
        Route::delete('/subject/delete/{id}', [SubjectController::class, 'destroy']);
        Route::post('/subjects/update/{id}', [SubjectController::class, 'update']);

        Route::get('grade/subjects/show', [GradeSubjectController::class, 'index']);
        Route::delete('grade/subject/delete/{id}', [GradeSubjectController::class, 'destroy']);
        Route::post('grade/subject/store', [GradeSubjectController::class, 'store']);
        Route::get('grade/subjects/show/{id}', [GradeSubjectController::class, 'show']);
        Route::post('grade/subjects/update/{id}', [GradeSubjectController::class, 'update']);

        Route::get('assessment/subjects/show', [AssessmentComponentController::class, 'index']);
        Route::get('assessment/subject/show/{id}', [AssessmentComponentController::class, 'show']);
        Route::post('assessment/subject/store', [AssessmentComponentController::class, 'store']);
        Route::post('assessment/subject/update/{id}', [AssessmentComponentController::class, 'update']);
        Route::delete('assessment/subject/delete/{id}', [AssessmentComponentController::class, 'destroy']);
        Route::get('assessment/subjects/grouped', [AssessmentComponentController::class, 'groupedBySubject']);
    });
});

Route::prefix('admin/settings/general')->middleware('auth:sanctum')->group(function () {

    Route::get('/', [SchoolSettingsController::class, 'show']);
    Route::get('/basic', [SchoolSettingsController::class, 'index']);
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
    Route::get('/policies', [FinancialSettingsController::class, 'getPolicies']);
    Route::post('/policies', [FinancialSettingsController::class, 'storePolicy']);

    // خطط الرسوم الموحدة
    Route::get('/fee-plans', [FinancialSettingsController::class, 'getFeePlans']);
    Route::post('/fee-plans', [FinancialSettingsController::class, 'storeFeePlan']);
    Route::post('/policies/{id}', [FinancialSettingsController::class, 'updatePolicy']);
    Route::delete('/policies/{id}', [FinancialSettingsController::class, 'destroyPolicy']);

    // تعديل وحذف خطط الرسوم الموحدة
    Route::post('/fee-plans/{id}', [FinancialSettingsController::class, 'updateFeePlan']);
    Route::delete('/fee-plans/{id}', [FinancialSettingsController::class, 'destroyFeePlan']);
    Route::post('/policy-items/{id}', [FinancialSettingsController::class, 'updatePolicyItem']);
    Route::delete('/policy-items/{id}', [FinancialSettingsController::class, 'destroyPolicyItem']);

    Route::post('/extra-services/{id}', [FinancialSettingsController::class, 'updateExtraService']);
    Route::delete('/extra-services/{id}', [FinancialSettingsController::class, 'destroyExtraService']);
    Route::get('/policies/{id}', [FinancialSettingsController::class, 'showPolicy']);
    Route::get('/fee-plans/{id}', [FinancialSettingsController::class, 'showFeePlan']);
    Route::get('/policy-items/{id}', [FinancialSettingsController::class, 'showPolicyItem']);
    Route::get('/extra-services/{id}', [FinancialSettingsController::class, 'showExtraService']);
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

Route::middleware('auth:sanctum', 'role:super_admin')->prefix('role')->group(function () {
    Route::get('/systemRoles', [RoleController::class, 'index']);
    Route::get('/systemModules', [RoleController::class, 'getSystemModules']);
    Route::put('/{id}/permissions', [RoleController::class, 'sync']);
});

Route::middleware('auth:sanctum')->prefix('admin/student')->group(function () {

    Route::post('/register', [StudentController::class, 'store']);
    Route::post('/import', [StudentController::class, 'importExcel']);
    Route::get('/import-batches/{batch}/errors/export', [StudentController::class, 'exportErrors'])
        ->middleware('can:student:create');
    Route::get('/import-batches/{batch}/status', [StudentController::class, 'getImportStatus'])
        ->middleware('can:student:create');
    Route::get('/import-batches/history', [StudentController::class, 'getBatchesHistory']);
});

Route::middleware(['auth:sanctum'])->prefix('admin/students')->group(function () {

    Route::get('/filter', [StudentController::class, 'filter'])
        ->middleware('can:student:view_profile');
    Route::get('/search', [StudentController::class, 'search'])
        ->middleware('can:student:view_profile');
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
    Route::post('/{enrollment}/student/restore', [StudentController::class, 'restore']);

});

Route::middleware('auth:sanctum')->prefix('admin/staff')->group(function () {

    Route::post('/register', [StaffController::class, 'store']);

    Route::post('/import/{role}', [StaffController::class, 'importExcel']);
    Route::get('/import-batches/{batch}/errors/export', [StaffController::class, 'exportErrors']);
    Route::get('/import-batches/{batch}/status', [StaffController::class, 'getImportStatus']);

    Route::get('/role/{role}/search', [StaffController::class, 'search']);
    Route::get('/alphabetical', [StaffController::class, 'alphabetical']);

    Route::get('/showAllStaff', [StaffController::class, 'index']);
    Route::get('/showStaff/{staffId}', [StaffController::class, 'show']);

    Route::post('/{staff}/personal', [StaffController::class, 'updatePersonal']);

    Route::get('/counts/roles', [StaffController::class, 'roleCounts']);
    Route::get('/role/{role}', [StaffController::class, 'getByRole']);

    Route::get('/profile', [StaffController::class, 'myProfile']);

    Route::post('/{staff}/toggle-status', [StaffController::class, 'toggleStatus']);
    Route::delete('/{staff}', [StaffController::class, 'destroy']);

    Route::post('/{staff}/workloads', [StaffController::class, 'setWorkload']);
    Route::get('/{staff}/workloads', [StaffController::class, 'getWorkloads']);
    Route::post('/{staff}/workloads/{workload}', [StaffController::class, 'updateWorkload']);
    Route::delete('/{staff}/workloads/{workload}', [StaffController::class, 'destroyWorkload']);

    Route::post('/{staff}/assignments', [StaffController::class, 'assignClassrooms']);
    Route::get('/{staff}/assignments', [StaffController::class, 'getAssignments']);
    Route::post('/{staff}/assignments/{assignment}', [StaffController::class, 'updateAssignment']);
    Route::delete('/{staff}/assignments/{assignment}', [StaffController::class, 'destroyAssignment']);
    Route::post('/{staff}/restore', [StaffController::class, 'restore']);
});
Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('attendance-settings')->controller(StudentAttendanceSettingController::class)->group(function () {

        Route::get('/', 'index');
        Route::get('/semester/{semester_id}', 'getBySemester');
        Route::post('/', 'store');
        Route::post('/{id}', 'update');
        Route::delete('/{id}', 'destroy');

    });
});
Route::middleware(['auth:sanctum'])->prefix('admin/attendance')->group(function () {
    // إدخال جماعي
    Route::post('/bulk', [StudentAttendanceController::class, 'storeBulk']);
    Route::get('/getRecord/{id}',[StudentAttendanceController::class,'get']);
    
    Route::get('/filter', [StudentAttendanceController::class, 'index']);
    
    Route::post('/record/{id}', [StudentAttendanceController::class, 'update']);
    
    Route::delete('/record/{id}', [StudentAttendanceController::class, 'destroy']);
});




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
        Route::get('/activity-unread-count', [ActivityController::class, 'getUnreadCount']);
        Route::post('/activity-mark-all-read', [ActivityController::class, 'markAllAsRead']);
        Route::get('/personal-image-url', [UserController::class, 'myPersonalPhotoUrl']);
        Route::get('/guardian/student/{studentId}/photo', [UserController::class, 'childPersonalPhotoUrl']);
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
        Route::get('/show/own/homeworks', [HomeworkController::class, 'studentIndex'])->middleware('role:student');
        Route::get('/show/child/homeworks/{id}', [HomeworkController::class, 'guardianChildIndex'])->middleware('role:guardian');
        Route::get('/homeworks/unread-count', [HomeworkController::class, 'unreadCount']);
        Route::post('/homeworks/mark-all-read', [HomeworkController::class, 'markAllAsRead']);
        Route::get('/show/own/evaluations', [ClassStudentEvaluationController::class, 'studentIndex']);
        Route::get('/show/child/evaluations/{id}', [ClassStudentEvaluationController::class, 'guardianChildIndex']);
        Route::get('/evaluation/unread-count', [ClassStudentEvaluationController::class, 'unreadCount']);
        Route::post('/evaluation/mark-all-read', [ClassStudentEvaluationController::class, 'markAllAsRead']);
        Route::get('/school/laws/all/show', [SchoolLawController::class, 'index']);
        Route::get('/school/law/one/show/{id}', [SchoolLawController::class, 'show']);
        Route::get('/marks/show/all', [StudentMarkDisplayController::class, 'index']);
        Route::get('/marks/unread-count', [StudentMarkDisplayController::class, 'unreadCount']);
        Route::post('/marks/mark-all-read', [StudentMarkDisplayController::class, 'markAllAsRead']);

        Route::middleware('role:student')->group(function () {
            Route::prefix('practice-quizzes')->controller(StudentPracticeQuizController::class)->group(function () {

                Route::get('/student/subjects', 'getSubjects');
                Route::get('/show/quiz/by/subjects/{gradeSubjectId}', 'getQuizzesBySubject');
                Route::get('/quiz/unread-count', 'unreadCount');
                Route::post('/quiz/mark-all-read', 'markAllRead');
                Route::post('/quiz/result/submit', 'submit');
                Route::get('/show/quiz/{id}', 'show');
                Route::get('/show/last/quiz/attempt/{quizId}', 'getLastAttemptDetails');

            });

            Route::prefix('helper/materials')->controller(StudentMaterialController::class)->group(function () {
                Route::get('/show/all-by/{gradeSubjectId}', 'getBySubject');
                Route::get('/show/one/{id}', 'show');
                Route::get('/download/{id}', 'download');
                Route::get('/count/unread', 'unreadCount');
                Route::post('/mark/all/read/', 'markAllRead');
            });
        });

    });

});

Route::prefix('admin')->group(function () {


    Route::post(
        '/schedules/generate',
        [
            ScheduleController::class,
            'generate'
        ]
    );


});


