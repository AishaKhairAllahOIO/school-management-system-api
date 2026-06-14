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
        // ─── مسح الـ Cache أولاً (ضروري مع Spatie) ──────────────────────
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── تعريف جميع الصلاحيات مقسّمة بالموديول ──────────────────────
        $permissions = [

            // ═══════════════════════════════════════════
            // MODULE 1: Authentication & Profile
            // ═══════════════════════════════════════════
            'auth' => [
                'login',
                'logout',
                'reset_password',
                'view_own_profile',
                'edit_own_profile'
            ],

            // ═══════════════════════════════════════════
            // MODULE 2: Student Management
            // ═══════════════════════════════════════════
            'students' => [
                'create_student',
                'edit_student',
                'delete_student',
                'change_student_account_status',
                'change_student_recourd_status',
                'view_students_by_class',
                'view_students_by_section',
                'view_student_profile',
                'search_student',
                'filter_students_alphabetically',
                'transfer_student_between_sections',
                'promote_students_to_next_grade',
                'view_children_list',          // Guardian
            ],

            // ═══════════════════════════════════════════
            // MODULE 3: Teacher Management
            // ═══════════════════════════════════════════
            'teachers' => [
                'create_teacher',
                'edit_teacher',
                'delete_teacher',
                'change_teacher_account_status',
                'change_teacher_recourd_status',
                'view_teachers_list',
                'view_teacher_profile',
                'search_teacher',
                'filter_teachers_by_subject_or_class',
                'assign_teacher_to_section',
                'assign_teacher_to_subject',
            ],

            // ═══════════════════════════════════════════
            // MODULE 4: Adviser Management
            // ═══════════════════════════════════════════
            'advisers' => [
                'create_adviser',
                'edit_adviser',
                'delete_adviser',
                'change_adviser_account_status',
                'change_adviser_recourd_status',
                'view_advisers_list',
                'view_adviser_profile',
                'search_adviser',
                'assign_adviser_to_class',
            ],

            // ═══════════════════════════════════════════
            // MODULE 5: Secretary Management
            // ═══════════════════════════════════════════
            'secretaries' => [
                'create_secretary',
                'edit_secretary',
                'delete_secretary',
                'change_secretary_account_status',
                'change_secretary_recourd_status',
                'view_secretary_profile',
            ],

            // ═══════════════════════════════════════════
            // MODULE 6: Service & Educational Staff
            // ═══════════════════════════════════════════
            'staff' => [
                'create_service_staff',
                'edit_service_staff',
                'delete_service_staff',
                'view_service_staff_list',
                'view_service_staff_profile',
                'create_educational_staff',
                'edit_educational_staff',
                'delete_educational_staff',
                'disable_educational_staff',
                'view_educational_staff_list',
                'view_educational_staff_profile',
            ],

            // ═══════════════════════════════════════════
            // MODULE 7: Attendance
            // ═══════════════════════════════════════════
            'attendance' => [
                'create_student_attendance',
                'edit_student_attendance',
                'view_student_attendance',
                'create_teacher_attendance',
                'edit_teacher_attendance',
                'view_teacher_attendance',
                'create_adviser_attendance',
                'edit_adviser_attendance',
                'view_adviser_attendance',
                'create_secretary_attendance',
                'edit_secretary_attendance',
                'view_secretary_attendance',
                'create_service_staff_attendance',
                'edit_service_staff_attendance',
                'view_service_staff_attendance',
                'create_educational_staff_attendance',
                'edit_educational_staff_attendance',
                'view_educational_staff_attendance',
                'set_absence_limit',
            ],

            // ═══════════════════════════════════════════
            // MODULE 8: Grades & Assignments
            // ═══════════════════════════════════════════
            'grades' => [
                'create_grades',
                'edit_grades',
                'delete_grades',
                'view_grades',
                'submit_grades_to_adviser',
                'create_assignment',
                'edit_assignment',
                'delete_assignment',
                'view_assignments',
                'view_top_students',
            ],

            // ═══════════════════════════════════════════
            // MODULE 9: Evaluations
            // ═══════════════════════════════════════════
            'evaluations' => [
                'create_student_evaluation',
                'edit_student_evaluation',
                'delete_student_evaluation',
                'view_student_evaluation',
                'submit_evaluation_to_adviser',
            ],

            // ═══════════════════════════════════════════
            // MODULE 10: Fees & Payments
            // ═══════════════════════════════════════════
            'fees' => [
                'set_annual_fees_per_class',
                'edit_annual_fees',
                'view_annual_fees',
                'set_installment_policy',
                'edit_installment_policy',
                'view_installment_policy',
                'create_payment',
                'edit_payment',
                'delete_payment',
                'view_payment_records',
                'view_remaining_fees',
                'send_payment_delay_notification',
            ],

            // ═══════════════════════════════════════════
            // MODULE 11: Salaries
            // ═══════════════════════════════════════════
            'salaries' => [
                'set_teacher_salary',
                'create_teacher_salary',
                'edit_teacher_salary',
                'delete_teacher_salary',
                'view_teacher_salary_records',
                'set_adviser_salary',
                'create_adviser_salary',
                'edit_adviser_salary',
                'delete_adviser_salary',
                'view_adviser_salary_records',
                'set_secretary_salary',
                'create_secretary_salary',
                'delete_secretary_salary',
                'view_secretary_salary_records',
                'set_service_staff_salary',
                'create_service_staff_salary',
                'edit_service_staff_salary',
                'delete_service_staff_salary',
                'view_service_staff_salary_records',
                'set_educational_staff_salary',
                'create_educational_staff_salary',
                'edit_educational_staff_salary',
                'delete_educational_staff_salary',
                'view_educational_staff_salary_records',
                'view_counselor_salary_records',
                'apply_salary_deduction_admin_staff',
                'apply_salary_deduction_service_staff',
                'apply_salary_deduction_educational_staff',
                'apply_salary_deduction_teachers',
            ],

            // ═══════════════════════════════════════════
            // MODULE 12: Leave Management
            // ═══════════════════════════════════════════
            'leaves' => [
                'set_leave_quota_admin_staff',
                'edit_leave_quota_admin_staff',
                'set_leave_quota_service_staff',
                'edit_leave_quota_service_staff',
                'set_leave_quota_educational_staff',
                'edit_leave_quota_educational_staff',
                'set_leave_quota_teachers',
                'edit_leave_quota_teachers',
                'set_annual_holidays',
            ],

            // ═══════════════════════════════════════════
            // MODULE 13: Schedules
            // ═══════════════════════════════════════════
            'schedules' => [
                'create_school_sections',
                'set_section_capacity',
                'create_student_schedule',
                'create_teacher_schedule',
                'create_exam_schedule',
                'view_own_work_schedule',
                'view_student_schedule',
                'view_exam_schedule',
            ],

            // ═══════════════════════════════════════════
            // MODULE 14: Subjects
            // ═══════════════════════════════════════════
            'subjects' => [
                'create_subject',
                'edit_subject',
                'delete_subject',
                'view_subjects',
                'assign_subject_to_class',
            ],

            // ═══════════════════════════════════════════
            // MODULE 15: Announcements & Notifications
            // ═══════════════════════════════════════════
            'notifications' => [
                'create_announcement',
                'edit_announcement',
                'delete_announcement',
                'view_announcements',
                'send_absence_alert_to_guardian',
                'send_payment_alert_to_guardian',
                'receive_system_notifications',
            ],

            // ═══════════════════════════════════════════
            // MODULE 16: School Rules
            // ═══════════════════════════════════════════
            'rules' => [
                'create_school_rule',
                'edit_school_rule',
                'delete_school_rule',
                'view_school_rules',
            ],

            // ═══════════════════════════════════════════
            // MODULE 17: Activities
            // ═══════════════════════════════════════════
            'activities' => [
                'create_activity',
                'view_activities',
            ],

            // ═══════════════════════════════════════════
            // MODULE 18: Reports
            // ═══════════════════════════════════════════
            'reports' => [
                'generate_reports',
                'view_financial_reports',
                'view_attendance_reports',
                'view_grades_reports',
                'view_subject_performance_reports',
            ],

            // ═══════════════════════════════════════════
            // MODULE 19: Counseling Appointments
            // ═══════════════════════════════════════════
            'counseling' => [
                'set_available_counseling_times',
                'view_appointment_requests',
                'approve_appointment_request',
                'reject_appointment_request',
                'view_student_appointments',
                'delete_appointment',
                'change_counselor_account_status',
                'change_counselor_recourd_status',
                'send_appointment_cancellation_notice',
                'record_session_status',
                'book_appointment',
                'cancel_appointment',
                'view_own_appointments',
            ],

            // ═══════════════════════════════════════════
            // MODULE 20: Complaints
            // ═══════════════════════════════════════════
            'complaints' => [
                'submit_complaint_to_adviser',
                'view_complaints',
            ],

            // ═══════════════════════════════════════════
            // MODULE 21: AI & Productivity Tools
            // ═══════════════════════════════════════════
            'tools' => [
                'use_ai_assistant',
                'use_todo_list',
            ],
        ];

        // ─── إنشاء جميع الصلاحيات في قاعدة البيانات ─────────────────────
        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission],
                    ['guard_name' => 'sanctum']
                );
            }
        }

        $this->command->info('✅ تم إنشاء جميع الصلاحيات بنجاح');

        // ═══════════════════════════════════════════════════════════════════
        // إنشاء الأدوار وربط الصلاحيات بها
        // ═══════════════════════════════════════════════════════════════════

        // ─────────────────────────────────────────
        // ROLE 1: Super Admin
        // ─────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['guard_name' => 'sanctum']
        );

        $superAdmin->syncPermissions([
            // Auth
            'login',
            'logout',
            'reset_password',
            'view_own_profile',
            'edit_own_profile',
            // Students
            'create_student',
            'edit_student',
            'delete_student',
            'change_student_account_status',
            'change_student_recourd_status',
            'view_students_by_class',
            'view_students_by_section',
            'view_student_profile',
            'search_student',
            'filter_students_alphabetically',
            'transfer_student_between_sections',
            'promote_students_to_next_grade',
            // Teachers
            'create_teacher',
            'edit_teacher',
            'delete_teacher',
            'change_teacher_account_status',
            'change_teacher_recourd_status',
            'view_teachers_list',
            'view_teacher_profile',
            'search_teacher',
            'filter_teachers_by_subject_or_class',
            'assign_teacher_to_section',
            'assign_teacher_to_subject',
            // Advisers
            'create_adviser',
            'edit_adviser',
            'delete_adviser',
            'change_adviser_account_status',
            'change_adviser_recourd_status',
            'view_advisers_list',
            'view_adviser_profile',
            'search_adviser',
            'assign_adviser_to_class',
            // Secretaries
            'create_secretary',
            'edit_secretary',
            'delete_secretary',
            'change_secretary_account_status',
            'change_secretary_recourd_status',
            'view_secretary_profile',
            // Staff
            'create_service_staff',
            'edit_service_staff',
            'delete_service_staff',
            'view_service_staff_list',
            'view_service_staff_profile',
            'create_educational_staff',
            'edit_educational_staff',
            'delete_educational_staff',
            'disable_educational_staff',
            'view_educational_staff_list',
            'view_educational_staff_profile',
            // Attendance
            'create_student_attendance',
            'edit_student_attendance',
            'view_student_attendance',
            'create_teacher_attendance',
            'edit_teacher_attendance',
            'view_teacher_attendance',
            'create_adviser_attendance',
            'edit_adviser_attendance',
            'view_adviser_attendance',
            'create_secretary_attendance',
            'edit_secretary_attendance',
            'view_secretary_attendance',
            'create_service_staff_attendance',
            'edit_service_staff_attendance',
            'view_service_staff_attendance',
            'create_educational_staff_attendance',
            'edit_educational_staff_attendance',
            'view_educational_staff_attendance',
            'set_absence_limit',
            // Grades
            'view_grades',
            'view_top_students',
            // Fees
            'set_annual_fees_per_class',
            'edit_annual_fees',
            'view_annual_fees',
            'set_installment_policy',
            'edit_installment_policy',
            'view_installment_policy',
            'create_payment',
            'edit_payment',
            'delete_payment',
            'view_payment_records',
            'view_remaining_fees',
            'send_payment_delay_notification',
            // Salaries
            'set_teacher_salary',
            'create_teacher_salary',
            'edit_teacher_salary',
            'delete_teacher_salary',
            'view_teacher_salary_records',
            'set_adviser_salary',
            'create_adviser_salary',
            'edit_adviser_salary',
            'delete_adviser_salary',
            'view_adviser_salary_records',
            'set_secretary_salary',
            'create_secretary_salary',
            'delete_secretary_salary',
            'view_secretary_salary_records',
            'set_service_staff_salary',
            'create_service_staff_salary',
            'edit_service_staff_salary',
            'delete_service_staff_salary',
            'view_service_staff_salary_records',
            'set_educational_staff_salary',
            'create_educational_staff_salary',
            'edit_educational_staff_salary',
            'delete_educational_staff_salary',
            'view_educational_staff_salary_records',
            'apply_salary_deduction_admin_staff',
            'apply_salary_deduction_service_staff',
            'apply_salary_deduction_educational_staff',
            'apply_salary_deduction_teachers',
            // Leaves
            'set_leave_quota_admin_staff',
            'edit_leave_quota_admin_staff',
            'set_leave_quota_service_staff',
            'edit_leave_quota_service_staff',
            'set_leave_quota_educational_staff',
            'edit_leave_quota_educational_staff',
            'set_leave_quota_teachers',
            'edit_leave_quota_teachers',
            'set_annual_holidays',
            // Schedules
            'create_school_sections',
            'set_section_capacity',
            'create_student_schedule',
            'create_teacher_schedule',
            'create_exam_schedule',
            // Subjects
            'create_subject',
            'edit_subject',
            'delete_subject',
            'view_subjects',
            'assign_subject_to_class',
            // Notifications
            'create_announcement',
            'edit_announcement',
            'delete_announcement',
            'view_announcements',
            'send_absence_alert_to_guardian',
            'send_payment_alert_to_guardian',
            'receive_system_notifications',
            // Rules
            'create_school_rule',
            'edit_school_rule',
            'delete_school_rule',
            'view_school_rules',
            // Activities
            'create_activity',
            'view_activities',
            // Reports
            'generate_reports',
            'view_financial_reports',
            'view_attendance_reports',
            'view_grades_reports',
            'view_subject_performance_reports',
            // Complaints
            'view_complaints',
        ]);

        // ─────────────────────────────────────────
        // ROLE 2: Teacher
        // ─────────────────────────────────────────
        $teacher = Role::firstOrCreate(
            ['name' => 'teacher'],
            ['guard_name' => 'sanctum']
        );

        $teacher->syncPermissions([
            // Auth
            'login',
            'logout',
            'view_own_profile',
            'edit_own_profile',
            // Students (view only)
            'view_students_by_class',
            'view_students_by_section',
            'view_student_profile',
            // Attendance
            'view_student_attendance',
            'view_teacher_attendance',
            // Grades
            'create_grades',
            'edit_grades',
            'delete_grades',
            'view_grades',
            'submit_grades_to_adviser',
            'create_assignment',
            'edit_assignment',
            'delete_assignment',
            'view_assignments',
            'view_top_students',
            // Evaluations
            'create_student_evaluation',
            'edit_student_evaluation',
            'delete_student_evaluation',
            'view_student_evaluation',
            'submit_evaluation_to_adviser',
            // Salaries (view own)
            'view_teacher_salary_records',
            // Schedules
            'view_own_work_schedule',
            'view_exam_schedule',
            // Subjects
            'view_subjects',
            // Notifications
            'view_announcements',
            'receive_system_notifications',
        ]);

        // ─────────────────────────────────────────
        // ROLE 3: Secretary
        // ─────────────────────────────────────────
        $secretary = Role::firstOrCreate(
            ['name' => 'secretary'],
            ['guard_name' => 'sanctum']
        );

        $secretary->syncPermissions([
            // Auth
            'login',
            'logout',
            'reset_password',
            'view_own_profile',
            'edit_own_profile',
            // Schedules
            'view_own_work_schedule',
            // Notifications
            'receive_system_notifications',

        ]);

        // ─────────────────────────────────────────
        // ROLE 4: Adviser
        // ─────────────────────────────────────────
        $adviser = Role::firstOrCreate(
            ['name' => 'adviser'],
            ['guard_name' => 'sanctum']
        );

        $adviser->syncPermissions([
            // Auth
            'login',
            'logout',
            'reset_password',
            'view_own_profile',
            'edit_own_profile',
            // Students
            'view_students_by_class',
            'view_students_by_section',
            'view_student_profile',
            // Attendance
            'view_student_attendance',
            // Grades
            'view_grades',
            'view_top_students',
            'view_student_evaluation',
            // Schedules
            'view_own_work_schedule',
            // Notifications
            'create_announcement',
            'edit_announcement',
            'delete_announcement',
            'view_announcements',
            'receive_system_notifications',
            // Rules
            'view_school_rules',
            // Activities
            'create_activity',
            'view_activities',
            // Reports
            'view_attendance_reports',
            'view_grades_reports',
            'view_subject_performance_reports',
            // Complaints
            'view_complaints',
        ]);

        // ─────────────────────────────────────────
        // ROLE 5: Counselor
        // ─────────────────────────────────────────
        $counselor = Role::firstOrCreate(
            ['name' => 'counselor'],
            ['guard_name' => 'sanctum']
        );

        $counselor->syncPermissions([
            // Auth
            'login',
            'logout',
            'view_own_profile',
            // Salaries (view own)
            'view_counselor_salary_records',
            // Schedules
            'view_own_work_schedule',
            // Counseling
            'set_available_counseling_times',
            'view_appointment_requests',
            'approve_appointment_request',
            'reject_appointment_request',
            'view_student_appointments',
            'delete_appointment',
            'send_appointment_cancellation_notice',
            'record_session_status',
            // Notifications
            'receive_system_notifications',
        ]);

        // ─────────────────────────────────────────
        // ROLE 6: Guardian
        // ─────────────────────────────────────────
        $guardian = Role::firstOrCreate(
            ['name' => 'guardian'],
            ['guard_name' => 'sanctum']
        );

        $guardian->syncPermissions([
            // Auth
            'login',
            'logout',
            'view_own_profile',
            // Students
            'view_children_list',
            // Schedules
            'view_student_schedule',
            'view_exam_schedule',
            // Grades
            'view_grades',
            'view_top_students',
            'view_assignments',
            'view_student_evaluation',
            // Fees
            'view_annual_fees',
            'view_payment_records',
            'view_remaining_fees',
            // Notifications
            'view_announcements',
            'receive_system_notifications',
            // Rules
            'view_school_rules',
            // Activities
            'view_activities',
            // Complaints
            'submit_complaint_to_adviser',
        ]);

        // ─────────────────────────────────────────
        // ROLE 7: Student
        // ─────────────────────────────────────────
        $student = Role::firstOrCreate(
            ['name' => 'student'],
            ['guard_name' => 'sanctum']
        );

        $student->syncPermissions([
            // Auth
            'login',
            'logout',
            'view_own_profile',
            // Schedules
            'view_student_schedule',
            'view_exam_schedule',
            // Grades
            'view_grades',
            'view_top_students',
            'view_assignments',
            'view_student_evaluation',
            // Notifications
            'view_announcements',
            'receive_system_notifications',
            // Rules
            'view_school_rules',
            // Activities
            'view_activities',
            // Counseling
            'book_appointment',
            'cancel_appointment',
            'view_own_appointments',
            // Tools
            'use_ai_assistant',
            'use_todo_list',
        ]);


        // ─────────────────────────────────────────
        // ROLE 8: Service_Staff
        // ─────────────────────────────────────────
        $service_staff = Role::firstOrCreate(
            ['name' => 'service_staff'],
            ['guard_name' => 'sanctum']
        );

       // $service_staff->syncPermissions();

        $this->command->info('✅ تم إنشاء جميع الأدوار وربط الصلاحيات بنجاح');
        $this->command->table(
            ['الدور', 'عدد الصلاحيات'],
            [
                ['super_admin', $superAdmin->permissions()->count()],
                ['teacher',     $teacher->permissions()->count()],
                ['secretary',   $secretary->permissions()->count()],
                ['adviser',     $adviser->permissions()->count()],
                ['counselor',   $counselor->permissions()->count()],
                ['guardian',    $guardian->permissions()->count()],
                ['student',     $student->permissions()->count()],
            ]
        );
    }
}
