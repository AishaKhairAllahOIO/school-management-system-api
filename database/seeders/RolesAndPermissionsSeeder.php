<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // 1. نظام المصادقة والحسابات الأساسية
            'auth' => [
                'login', 'logout', 'reset_password', 
                'view_own_profile', 'edit_own_profile',
                'account:toggle_status', 'record:toggle_status'
            ],
            // 1.1 تهيئة النظام
            'school_setup' => [
                'school:initialize',      
                'school:update_info',     
                'academic_year:manage',   
            ],
            'access_control' => [
            'role:create', 'role:edit', 'role:delete', 'role:view',
            'permission:view', 'permission:assign_to_role'
], 
            // 2. إدارة الطلاب والتسجيل
            'students' => [
                'student:create', 'student:edit', 'student:delete',
                'student:view_by_class', 'student:view_by_section', 'student:view_profile',
                'student:search', 'student:filter', 'student:transfer', 'student:promote',
                'student:view_children' 
            ],

            // 3. إدارة الكوادر
            'users_management' => [
                'teacher:create', 'teacher:edit', 'teacher:delete', 'teacher:view', 'teacher:assign_section', 'teacher:assign_subject',
                'adviser:create', 'adviser:edit', 'adviser:delete', 'adviser:view', 'adviser:assign_class',
                'secretary:create', 'secretary:edit', 'secretary:delete', 'secretary:view',
                'service_staff:create', 'service_staff:edit', 'service_staff:delete', 'service_staff:view',
                'counselor:create', 'counselor:edit', 'counselor:delete', 'counselor:view'
            ],

            // 4. نظام الحضور والغياب
            'attendance' => [
                'attendance_student:create', 'attendance_student:edit', 'attendance_student:view', 'attendance:set_limit',
                'attendance_teacher:create', 'attendance_teacher:edit', 'attendance_teacher:view',
                'attendance_adviser:create', 'attendance_adviser:edit', 'attendance_adviser:view',
                'attendance_secretary:create', 'attendance_secretary:edit', 'attendance_secretary:view',
                'attendance_service:create', 'attendance_service:edit', 'attendance_service:view',
                'attendance_counselor:create', 'attendance_counselor:edit', 'attendance_counselor:view'
            ],

            // 5. نظام العلامات والتقييمات
            'academic_records' => [
                'mark:create', 'mark:edit', 'mark:delete', 'mark:view','mark:publish', 'mark:submit_to_adviser',
                'evaluation:create', 'evaluation:edit', 'evaluation:delete', 'evaluation:view', 'evaluation:submit_to_adviser',
                'view_top_students'
            ],

            // 6. نظام الوظائف
            'homework' => [
                'homework:create', 'homework:edit', 'homework:delete', 'homework:view'
            ],

            // 7. الرسوم المالية
            'finance' => [
                'fee:set', 'fee:edit', 'fee:view',
                'installment:set_policy', 'installment:edit_policy', 'installment:view_policy',
                'payment:create', 'payment:edit', 'payment:delete', 'payment:view_records', 'payment:view_remaining'
            ],

            // 8. الرواتب والأجور
            'salaries' => [
                'salary_teacher:set', 'salary_teacher:create', 'salary_teacher:edit', 'salary_teacher:delete', 'salary_teacher:view',
                'salary_adviser:set', 'salary_adviser:create', 'salary_adviser:edit', 'salary_adviser:delete', 'salary_adviser:view',
                'salary_secretary:set', 'salary_secretary:create', 'salary_secretary:delete', 'salary_secretary:view',
                'salary_service:set', 'salary_service:create', 'salary_service:edit', 'salary_service:delete', 'salary_service:view',
                'salary_counselor:set', 'salary_counselor:create', 'salary_counselor:edit', 'salary_counselor:delete', 'salary_counselor:view',
                'salary:view_own' 
            ],

            // 9. الإجازات 
            'leaves' => [
                'leave_admin:set_quota', 'leave_admin:edit_quota', 'leave_admin:apply_deduction', 
                'leave_service:set_quota', 'leave_service:edit_quota', 'leave_service:apply_deduction',
                'leave_counselor:set_quota', 'leave_counselor:edit_quota', 'leave_counselor:apply_deduction',
                'leave_teacher:set_quota', 'leave_teacher:edit_quota', 'leave_teacher:apply_deduction',
                'leave:set_annual_holidays'
            ],

            // 10. الهيكلية والجداول الدراسية (تم توحيد عرض البرنامج الشخصي)
            'schedules' => [
                'section:create', 'section:set_capacity', 'section:config_numbers',
                'schedule_student:create', 'schedule_student:view',
                'schedule_teacher:create', 
                'schedule:view_own', // توحيد اسم الصلاحية للموظفين
                'schedule_exam:create', 'schedule_exam:view'
            ],

            // 11. المواد الدراسية
            'subjects' => [
                'subject:create', 'subject:edit', 'subject:delete', 'subject:view', 'subject:assign_to_class'
            ],

            // 12. الإعلانات والتنبيهات
            'notifications' => [
                'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
                'alert_absence:send', 'alert_payment:send', 'notification:system_receive'
            ],

            // 13. القوانين والأنشطة والتقارير
            'school_rules' => [
                'rule:create', 'rule:edit', 'rule:delete', 'rule:view',
                'activity:create', 'activity:view',
                'report:generate', 'report_financial:view', 'report_attendance:view', 'report_grades:view', 'report_performance:view'
            ],

            // 14. الاستشارات
            'counseling' => [
                'counseling:set_times', 'counseling:view_requests', 'counseling:approve', 'counseling:reject',
                'counseling:view_appointments', 'counseling:delete', 'counseling:cancel_notice', 'counseling:record_status',
                'counseling:book', 'counseling:cancel', 'counseling:view_own'
            ],

            // 15. الشكاوى
            'complaints' => [
                'complaint:submit_to_adviser', 'complaint:view'
            ],

            // 16. أدوات الذكاء الاصطناعي
            'tools' => [
                'tool:ai_assistant', 'tool:todo_list'
            ]
        ];

        // 1. إنشاء الصلاحيات
        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission],
                    ['guard_name' => 'sanctum']
                );
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 2. ربط الصلاحيات بالأدوار
        // ═══════════════════════════════════════════════════════════════════

        // ROLE 1: Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin'], ['guard_name' => 'sanctum']);
        $superAdmin->syncPermissions(Permission::whereNotIn('name', [
            'counseling:book', 'counseling:cancel', 'counseling:view_own', 'tool:ai_assistant', 'tool:todo_list'
        ])->pluck('name'));

        // ROLE 2: Teacher (تمت إضافة الغياب الفعلي والشكاوى وبرنامج الدوام)
        $teacher = Role::firstOrCreate(['name' => 'teacher'], ['guard_name' => 'sanctum']);
        $teacher->syncPermissions([
            'login', 'logout', 'view_own_profile', 'edit_own_profile',
            'student:view_by_class', 'student:view_by_section', 'student:view_profile',
            'attendance_student:create', 'attendance_student:edit', 'attendance_student:view', 'attendance_teacher:view',
            'mark:create', 'mark:edit', 'mark:delete', 'mark:view', 'mark:submit_to_adviser',
            'homework:create', 'homework:edit', 'homework:delete', 'homework:view',
            'evaluation:create', 'evaluation:edit', 'evaluation:delete', 'evaluation:view', 'evaluation:submit_to_adviser',
            'salary:view_own', 'schedule:view_own', 'schedule_exam:view', 'subject:view',
            'announcement:view', 'notification:system_receive', 'rule:view', 'complaint:submit_to_adviser'
        ]);

        // ROLE 3: Secretary
        $secretary = Role::firstOrCreate(['name' => 'secretary'], ['guard_name' => 'sanctum']);
        $secretary->syncPermissions([
            'login', 'logout', 'reset_password', 'view_own_profile', 'edit_own_profile',
            'student:create', 'student:edit', 'student:delete', 'student:view_by_class', 
            'student:view_by_section', 'student:view_profile', 'student:search', 
            'student:filter', 'student:transfer', 'student:promote',
            'teacher:create', 'teacher:edit', 'teacher:view',
            'adviser:create', 'adviser:edit', 'adviser:view',
            'service_staff:create', 'service_staff:edit', 'service_staff:view',
            'counselor:create', 'counselor:edit', 'counselor:view',
            'salary_teacher:set', 'salary_teacher:create', 'salary_teacher:edit', 'salary_teacher:delete', 'salary_teacher:view',
            'salary_adviser:set', 'salary_adviser:create', 'salary_adviser:edit', 'salary_adviser:delete', 'salary_adviser:view',
            'salary_secretary:set', 'salary_secretary:create', 'salary_secretary:delete', 'salary_secretary:view',
            'salary_service:set', 'salary_service:create', 'salary_service:edit', 'salary_service:delete', 'salary_service:view',
            'salary_counselor:set', 'salary_counselor:create', 'salary_counselor:edit', 'salary_counselor:delete', 'salary_counselor:view',
            'salary:view_own',
            'schedule_student:view', 'schedule_exam:view', 'schedule:view_own',
            'notification:system_receive'
        ]);

        // ROLE 4: Adviser (تم إصلاح خطأ السلسلة الفارغة وتعديل جدول الدوام)
        $adviser = Role::firstOrCreate(['name' => 'adviser'], ['guard_name' => 'sanctum']);
        $adviser->syncPermissions([
            'login', 'logout', 'reset_password', 'view_own_profile', 'edit_own_profile',
            'student:view_by_class', 'student:view_by_section', 'student:view_profile',
            'attendance_student:create','attendance_student:edit', 'attendance_student:view',
            'mark:view','mark:publish', 'view_top_students', 'evaluation:view',
            'schedule:view_own', 'announcement:create', 'announcement:edit', 'announcement:delete', 'announcement:view',
            'notification:system_receive', 'rule:view', 'activity:create', 'activity:view',
            'report_attendance:view', 'report_grades:view', 'report_performance:view', 'complaint:view', 'salary:view_own'
        ]);

        // ROLE 5: Counselor
        $counselor = Role::firstOrCreate(['name' => 'counselor'], ['guard_name' => 'sanctum']);
        $counselor->syncPermissions([
            'login', 'logout', 'view_own_profile', 'edit_own_profile',
            'counseling:set_times', 'counseling:view_requests', 'counseling:approve', 'counseling:reject',
            'counseling:view_appointments', 'counseling:delete', 'counseling:cancel_notice', 'counseling:record_status',
            'notification:system_receive', 'salary:view_own', 'schedule:view_own'
        ]);

        // ROLE 6: Guardian
        $guardian = Role::firstOrCreate(['name' => 'guardian'], ['guard_name' => 'sanctum']);
        $guardian->syncPermissions([
            'login', 'logout', 'view_own_profile', 'edit_own_profile',
            'student:view_children', 'schedule_student:view', 'schedule_exam:view',
            'mark:view', 'view_top_students', 'homework:view', 'evaluation:view',
            'fee:view', 'payment:view_records', 'payment:view_remaining',
            'announcement:view', 'notification:system_receive', 'rule:view', 'activity:view',
            'complaint:submit_to_adviser'
        ]);

        // ROLE 7: Student (تم إصلاح خطأ اسم جدول الامتحانات)
        $student = Role::firstOrCreate(['name' => 'student'], ['guard_name' => 'sanctum']);
        $student->syncPermissions([
            'login', 'logout', 'view_own_profile', 'edit_own_profile', 
            'schedule_student:view', 'schedule_exam:view', 'mark:view', 'view_top_students',
            'homework:view', 'evaluation:view', 'announcement:view', 'notification:system_receive',
            'rule:view', 'activity:view', 'counseling:book', 'counseling:cancel', 'counseling:view_own',
            'tool:ai_assistant', 'tool:todo_list'
        ]);
        $service_staff=Role::firstOrCreate(['name' => 'service_staff'], ['guard_name' => 'sanctum']);
        
        // مسح الكاش أوتوماتيكياً في نهاية السييدر لضمان عدم حدوث تعليق
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}