<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemModule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    // public function run(): void
    // {
    //     // 1. مسح الكاش أوتوماتيكياً لمنع التضارب
    //     app()[PermissionRegistrar::class]->forgetCachedPermissions();

    //     // ═══════════════════════════════════════════════════════════════════
    //     // 2. توزيع صلاحياتك الأصلية الدقيقة على الـ 8 موديولات للفرونت إند
    //     // ═══════════════════════════════════════════════════════════════════
    //     $modulesWithPermissions = [
    //         'Users' => [
    //             'name' => 'Users', 
    //             'perms' => [
    //                 'student:create', 'student:edit', 'student:delete', 'student:view_by_class', 'student:view_by_section', 'student:view_profile', 'student:search', 'student:filter', 'student:transfer', 'student:promote', 'student:view_children',
    //                 'teacher:create', 'teacher:edit', 'teacher:delete', 'teacher:view', 'teacher:assign_section', 'teacher:assign_subject',
    //                 'adviser:create', 'adviser:edit', 'adviser:delete', 'adviser:view', 'adviser:assign_class',
    //                 'secretary:create', 'secretary:edit', 'secretary:delete', 'secretary:view',
    //                 'service_staff:create', 'service_staff:edit', 'service_staff:delete', 'service_staff:view',
    //                 'counselor:create', 'counselor:edit', 'counselor:delete', 'counselor:view', 'staff:create'
    //             ]
    //         ],
    //         'Academics' => [
    //             'name' => 'Academics', 
    //             'perms' => [
    //                 'subject:create', 'subject:edit', 'subject:delete', 'subject:view', 'subject:assign_to_class',
    //                 'mark:create', 'mark:edit', 'mark:delete', 'mark:view', 'mark:publish', 'mark:submit_to_adviser',
    //                 'evaluation:create', 'evaluation:edit', 'evaluation:delete', 'evaluation:view', 'evaluation:submit_to_adviser', 'view_top_students',
    //                 'homework:create', 'homework:edit', 'homework:delete', 'homework:view',
    //                 'rule:create', 'rule:edit', 'rule:delete', 'rule:view',
    //                 'activity:create', 'activity:view'
    //             ]
    //         ],
    //         'Attendance' => [
    //             'name' => 'Attendance', 
    //             'perms' => [
    //                 'attendance_student:create', 'attendance_student:edit', 'attendance_student:view', 'attendance:set_limit',
    //                 'attendance_teacher:create', 'attendance_teacher:edit', 'attendance_teacher:view',
    //                 'attendance_adviser:create', 'attendance_adviser:edit', 'attendance_adviser:view',
    //                 'attendance_secretary:create', 'attendance_secretary:edit', 'attendance_secretary:view',
    //                 'attendance_service:create', 'attendance_service:edit', 'attendance_service:view',
    //                 'attendance_counselor:create', 'attendance_counselor:edit', 'attendance_counselor:view',
    //                 'leave_admin:set_quota', 'leave_admin:edit_quota', 'leave_admin:apply_deduction',
    //                 'leave_service:set_quota', 'leave_service:edit_quota', 'leave_service:apply_deduction',
    //                 'leave_counselor:set_quota', 'leave_counselor:edit_quota', 'leave_counselor:apply_deduction',
    //                 'leave_teacher:set_quota', 'leave_teacher:edit_quota', 'leave_teacher:apply_deduction',
    //                 'leave:set_annual_holidays'
    //             ]
    //         ],
    //         'Scheduling' => [
    //             'name' => 'Scheduling', 
    //             'perms' => [
    //                 'section:create', 'section:set_capacity', 'section:config_numbers',
    //                 'schedule_student:create', 'schedule_student:view',
    //                 'schedule_teacher:create', 'schedule:view_own',
    //                 'schedule_exam:create', 'schedule_exam:view'
    //             ]
    //         ],
    //         'Finance' => [
    //             'name' => 'Finance', 
    //             'perms' => [
    //                 'fee:set', 'fee:edit', 'fee:view',
    //                 'installment:set_policy', 'installment:edit_policy', 'installment:view_policy',
    //                 'payment:create', 'payment:edit', 'payment:delete', 'payment:view_records', 'payment:view_remaining',
    //                 'salary_teacher:set', 'salary_teacher:create', 'salary_teacher:edit', 'salary_teacher:delete', 'salary_teacher:view',
    //                 'salary_adviser:set', 'salary_adviser:create', 'salary_adviser:edit', 'salary_adviser:delete', 'salary_adviser:view',
    //                 'salary_secretary:set', 'salary_secretary:create', 'salary_secretary:delete', 'salary_secretary:view',
    //                 'salary_service:set', 'salary_service:create', 'salary_service:edit', 'salary_service:delete', 'salary_service:view',
    //                 'salary_counselor:set', 'salary_counselor:create', 'salary_counselor:edit', 'salary_counselor:delete', 'salary_counselor:view',
    //                 'salary:view_own'
    //             ]
    //         ],
    //         'Communications' => [
    //             'name' => 'Communications', 
    //             'perms' => [
    //                 'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
    //                 'alert_absence:send', 'alert_payment:send', 'notification:system_receive',
    //                 'counseling:set_times', 'counseling:view_requests', 'counseling:approve', 'counseling:reject',
    //                 'counseling:view_appointments', 'counseling:delete', 'counseling:cancel_notice', 'counseling:record_status',
    //                 'counseling:book', 'counseling:cancel', 'counseling:view_own',
    //                 'complaint:submit_to_adviser', 'complaint:view'
    //             ]
    //         ],
    //         'Reports' => [
    //             'name' => 'Reports', 
    //             'perms' => [
    //                 'report:generate', 'report_financial:view', 'report_attendance:view', 'report_grades:view', 'report_performance:view'
    //             ]
    //         ],
    //         'Settings' => [
    //             'name' => 'Settings', 
    //             'perms' => [
    //                 'login', 'logout', 'reset_password', 'view_own_profile', 'edit_own_profile', 'account:toggle_status', 'record:toggle_status',
    //                 'school:initialize', 'school:update_info', 'academic_year:manage',
    //                 'role:create', 'role:edit', 'role:delete', 'role:view', 'permission:view', 'permission:assign_to_role',
    //                 'tool:ai_assistant', 'tool:todo_list'
    //             ]
    //         ]
    //     ];

    //     // ═══════════════════════════════════════════════════════════════════
    //     // 3. البناء الديناميكي للموديولات والصلاحيات
    //     // ═══════════════════════════════════════════════════════════════════
    //     foreach ($modulesWithPermissions as $moduleKey => $moduleData) {
    //         $module = SystemModule::firstOrCreate(['name' => $moduleData['name']]);
    //         foreach ($moduleData['perms'] as $permissionName) {
    //             Permission::firstOrCreate(
    //                 ['name' => $permissionName, 'guard_name' => 'sanctum'],
    //                 [
    //                     'module_id'    => $module->id,
    //                     'access_level' => $this->inferAccessLevel($permissionName)
    //                 ]
    //             );
    //         }
    //     }

    //     // ═══════════════════════════════════════════════════════════════════
    //     // 4. تعيين الصلاحيات للأدوار تماماً كما برمجتيها سابقاً
    //     // ═══════════════════════════════════════════════════════════════════

    //     $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum'], ['is_system' => true]);
    //     $superAdmin->syncPermissions(Permission::all());

    //     $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);
    //     // $teacher->syncPermissions([
    //     //     'login', 'logout', 'view_own_profile',
    //     //     'student:view_by_class', 'student:view_by_section', 'student:view_profile',
    //     //     'attendance_student:create', 'attendance_student:edit', 'attendance_student:view', 'attendance_teacher:view',
    //     //     'mark:create', 'mark:edit', 'mark:delete', 'mark:view', 'mark:submit_to_adviser',
    //     //     'homework:create', 'homework:edit', 'homework:delete', 'homework:view',
    //     //     'evaluation:create', 'evaluation:edit', 'evaluation:delete', 'evaluation:view', 'evaluation:submit_to_adviser',
    //     //     'salary:view_own', 'schedule:view_own', 'schedule_exam:view', 'subject:view',
    //     //     'announcement:view', 'notification:system_receive', 'rule:view', 'complaint:submit_to_adviser'
    //     // ]);

    //     $secretary = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);
    //     $secretary->syncPermissions([
    //         'login', 'logout', 'reset_password', 'view_own_profile',
    //         'student:create', 'student:edit', 'student:delete', 'student:view_by_class',
    //         'student:view_by_section', 'student:view_profile', 'student:search',
    //         'student:filter', 'student:transfer', 'student:promote',
    //         'teacher:create', 'teacher:edit', 'teacher:view',
    //         'adviser:create', 'adviser:edit', 'adviser:view',
    //         'service_staff:create', 'service_staff:edit', 'service_staff:view',
    //         'counselor:create', 'counselor:edit', 'counselor:view',
    //         'salary_teacher:set', 'salary_teacher:create', 'salary_teacher:edit', 'salary_teacher:delete', 'salary_teacher:view',
    //         'salary_adviser:set', 'salary_adviser:create', 'salary_adviser:edit', 'salary_adviser:delete', 'salary_adviser:view',
    //         'salary_secretary:set', 'salary_secretary:create', 'salary_secretary:delete', 'salary_secretary:view',
    //         'salary_service:set', 'salary_service:create', 'salary_service:edit', 'salary_service:delete', 'salary_service:view',
    //         'salary_counselor:set', 'salary_counselor:create', 'salary_counselor:edit', 'salary_counselor:delete', 'salary_counselor:view',
    //         'salary:view_own',
    //         'schedule_student:view', 'schedule_exam:view', 'schedule:view_own',
    //         'notification:system_receive'
    //     ]);

    //     $adviser = Role::firstOrCreate(['name' => 'adviser', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);
    //     $adviser->syncPermissions([
    //         'login', 'logout', 'reset_password', 'view_own_profile', 
    //         'student:view_by_class', 'student:view_by_section', 'student:view_profile',
    //         'attendance_student:create','attendance_student:edit', 'attendance_student:view',
    //         'mark:view','mark:publish', 'view_top_students', 'evaluation:view',
    //         'schedule:view_own', 'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
    //         'notification:system_receive', 'rule:view', 'activity:create', 'activity:view',
    //         'report_attendance:view', 'report_grades:view', 'report_performance:view', 'complaint:view', 'salary:view_own'
    //     ]);

    //     $counselor = Role::firstOrCreate(['name' => 'counselor', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);
    //     // $counselor->syncPermissions([
    //     //     'login', 'logout', 'view_own_profile',
    //     //     'counseling:set_times', 'counseling:view_requests', 'counseling:approve', 'counseling:reject',
    //     //     'counseling:view_appointments', 'counseling:delete', 'counseling:cancel_notice', 'counseling:record_status',
    //     //     'notification:system_receive', 'salary:view_own', 'schedule:view_own'
    //     // ]);

    //     $guardian = Role::firstOrCreate(['name' => 'guardian', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);
    //     // $guardian->syncPermissions([
    //     //     'login', 'logout', 'view_own_profile',
    //     //     'student:view_children', 'schedule_student:view', 'schedule_exam:view',
    //     //     'mark:view', 'view_top_students', 'homework:view', 'evaluation:view',
    //     //     'fee:view', 'payment:view_records', 'payment:view_remaining',
    //     //     'announcement:view', 'notification:system_receive', 'rule:view', 'activity:view',
    //     //     'complaint:submit_to_adviser'
    //     // ]);

    //     $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);
    //     // $student->syncPermissions([
    //     //     'login', 'logout', 'view_own_profile',
    //     //     'schedule_student:view', 'schedule_exam:view', 'mark:view', 'view_top_students',
    //     //     'homework:view', 'evaluation:view', 'announcement:view', 'notification:system_receive',
    //     //     'rule:view', 'activity:view', 'counseling:book', 'counseling:cancel', 'counseling:view_own',
    //     //     'tool:ai_assistant', 'tool:todo_list'
    //     // ]);
        
    //     $service_staff = Role::firstOrCreate(['name' => 'service_staff', 'guard_name' => 'sanctum'], ['is_system' => true,'is_active'=>true]);

    //     app()[PermissionRegistrar::class]->forgetCachedPermissions();
    // }

    // private function inferAccessLevel(string $permissionName): string
    // {
    //     if (str_contains($permissionName, 'own') || str_contains($permissionName, 'profile') || str_contains($permissionName, 'children')) {
    //         return 'own';
    //     }
    //     if (str_contains($permissionName, 'class') || str_contains($permissionName, 'section')) {
    //         return 'section';
    //     }
    //     return 'global';
    // }
    public function run(): void
    {
        // 1. مسح الكاش أوتوماتيكياً لمنع التضارب
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ═══════════════════════════════════════════════════════════════════
        // 2. البنية المعتمدة والنظيفة للموديولات والصلاحيات الـ 8 (القاموس الشامل)
        // ═══════════════════════════════════════════════════════════════════
        $modulesWithPermissions = [
            'Users' => [
                'name' => 'Users', 
                'perms' => [
                    // الطلاب
                    'student:create', 'student:edit', 'student:delete', 'student:view', 'student:transfer', 'student:promote',
                    // الموظفون
                    'staff:create', 'staff:edit', 'staff:delete', 'staff:view',
                    // الحساب الشخصي
                    'login', 'logout', 'reset_password', 'view_own_profile', 'edit_own_profile'
                ]
            ],
            'Academics' => [
                'name' => 'Academics', 
                'perms' => [
                    // إدارة الهيكلية الأكاديمية
                    'academic_structure:create', 'academic_structure:edit', 'academic_structure:delete',
                    'academic_structure:view',
                    
                    // 💡 العلامات: استلام، نشر، ورؤية (بدون إمكانية التعديل/الإصدار المباشر)
                    'mark:submit_to_adviser', 'mark:publish', 'mark:view'
                ]
            ],
            'Attendance' => [
                'name' => 'Attendance', 
                'perms' => [
                    'attendance_student:create', 'attendance_student:edit', 'attendance_student:view', 'attendance:set_limit',
                    'attendance_staff:create', 'attendance_staff:edit', 'attendance_staff:view',
                    'leave:set_quota', 'leave:edit_quota', 'leave:set_annual_holidays'
                ]
            ],
            'Scheduling' => [
                'name' => 'Scheduling', 
                'perms' => [
                    'schedule_weekly:generate', 'schedule_weekly:create', 'schedule_weekly:edit', 'schedule_weekly:delete', 'schedule_weekly:view',  
                    
                    'schedule_exam:generate', 'schedule_exam:create', 'schedule_exam:edit', 'schedule_exam:delete', 'schedule_exam:view'     
                ]
            ],
            'Finance' => [
                'name' => 'Finance', 
                'perms' => [
                    // عمليات الصندوق والدفع اليومية والذمم
                    'payment:create', 'payment:edit', 'payment:delete', 'payment:view_records', 'payment:view_remaining',
                    // الرواتب
                    'salary:set', 'salary:create', 'salary:edit', 'salary:delete', 'salary:view', 'salary:view_own'
                ]
            ],
            'Communications' => [
                'name' => 'Communications', 
                'perms' => [
                    'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
                    'alert_absence:send', 'alert_payment:send', 'notification:system_receive', 'complaint:view',
                    // القوانين والأنشطة
                    'rule:create', 'rule:edit', 'rule:delete', 'rule:view',
                    'activity:create', 'activity:edit', 'activity:delete', 'activity:view'
                ]
            ],
            'Reports' => [
                'name' => 'Reports', 
                'perms' => [
                    'report:generate', 'report_financial:view', 'report_attendance:view'
                ]
            ],
            'Settings' => [
                'name' => 'Settings', 
                'perms' => [
                    // التهيئة العامة والأكاديمية والسيادية (للمدير العام)
                    'school:initialize', 'school:update_info', 'academic_year:manage',
                    'role:create', 'role:edit', 'role:delete', 'role:view', 'permission:view', 'permission:assign_to_role',
                    
                    // التهيئة المالية السنوية
                    'fee:set', 'fee:edit', 'fee:view',
                    'installment:set_policy', 'installment:edit_policy', 'installment:view_policy'
                ]
            ]
        ];

        // ═══════════════════════════════════════════════════════════════════
        // 3. إنشاء الموديولات والصلاحيات في قواعد البيانات
        // ═══════════════════════════════════════════════════════════════════
        foreach ($modulesWithPermissions as $moduleKey => $moduleData) {
            $module = SystemModule::firstOrCreate(['name' => $moduleData['name']]);
            foreach ($moduleData['perms'] as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'sanctum'],
                    [
                        'module_id'    => $module->id,
                        'access_level' => $this->inferAccessLevel($permissionName)
                    ]
                );
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 4. تعيين الصلاحيات المخصصة للأدوار الثلاثة الرئيسية (الويب)
        // ═══════════════════════════════════════════════════════════════════

        // 👑 1. المدير العام (Super Admin) - سيادة كاملة على كافة الصلاحيات
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        $superAdmin->syncPermissions(Permission::all());

        // 💼 2. أمين السر (Secretary)
        $secretary = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        $secretary->syncPermissions([
            // Users
            'student:create', 'student:edit', 'student:delete', 'student:view', 'student:transfer', 'student:promote',
            'staff:create', 'staff:edit', 'staff:delete', 'staff:view',
            'login', 'logout', 'reset_password', 'view_own_profile', 'edit_own_profile',

            // Academics 💡 (رؤية الهيكلية + استعلام ونشر ورؤية العلامات)
            'academic_structure:view',
            'mark:publish', 'mark:view',

            // Attendance
            'attendance_student:view', 
            'attendance_staff:create', 'attendance_staff:edit', 'attendance_staff:view',

            // Scheduling
            'schedule_weekly:view', 'schedule_exam:view',

            // Finance
            'payment:create', 'payment:edit', 'payment:delete', 'payment:view_records', 'payment:view_remaining',
            'salary:set', 'salary:create', 'salary:edit', 'salary:delete', 'salary:view', 'salary:view_own',

            // Communications
            'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
            'alert_absence:send', 'alert_payment:send', 'notification:system_receive',
            'rule:view', 
            'activity:create', 'activity:edit', 'activity:delete', 'activity:view',

            // Reports
            'report:generate', 'report_financial:view', 'report_attendance:view',

            // Settings
            'fee:set', 'fee:edit', 'fee:view',
            'installment:set_policy', 'installment:edit_policy', 'installment:view_policy'
        ]);

        // 🧑‍🏫 3. الموجه (Adviser)
        $adviser = Role::firstOrCreate(['name' => 'adviser', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        $adviser->syncPermissions([
            // Users
            'student:view', 'staff:view',
            'login', 'logout', 'reset_password', 'view_own_profile', 'edit_own_profile',

            // Academics 💡 (رؤية الهيكلية + استلام ونشر ورؤية العلامات)
            'academic_structure:view',
             'mark:publish', 'mark:view',

            // Attendance
            'attendance_student:create', 'attendance_student:edit', 'attendance_student:view',
            'attendance_staff:create', 'attendance_staff:edit', 'attendance_staff:view',

            // Scheduling
            'schedule_weekly:view', 'schedule_exam:view',

            // Finance
            'salary:view_own',

            // Communications
            'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
            'alert_absence:send', 'notification:system_receive', 'complaint:view',
            'rule:view', 
            'activity:create', 'activity:edit', 'activity:delete', 'activity:view',

            // Reports
            'report:generate', 'report_attendance:view', 'report_financial:view' 
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // 5. إنشاء بقية الأدوار (بدون إسناد صلاحيات لها حسب الطلب)
        // ═══════════════════════════════════════════════════════════════════
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        Role::firstOrCreate(['name' => 'counselor', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        Role::firstOrCreate(['name' => 'guardian', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);
        Role::firstOrCreate(['name' => 'service_staff', 'guard_name' => 'sanctum'], ['is_system' => true, 'is_active' => true]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function inferAccessLevel(string $permissionName): string
    {
        if (str_contains($permissionName, 'own') || str_contains($permissionName, 'profile')) {
            return 'own';
        }
        if (str_contains($permissionName, 'class') || str_contains($permissionName, 'section')) {
            return 'section';
        }
        return 'global';
    }
} 