<?php

namespace App\Services\Dashboard;

use App\Models\Student;
use App\Models\Staff;
use App\Models\ClassRoom;
use App\Models\AcademicStage;
use App\Models\StudentAttendance;
use App\Models\ScheduledInstallment;
use App\Models\PaymentTransaction;
use App\Models\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class DashboardService
{
    /**
     * 👑 1. إحصائيات المدير العام (Super Admin Dashboard)
     */
    public function getSuperAdminDashboard(): array
    {
        return [
            'overview' => [
                'students_count' => Student::count(),
                'teachers_count' => $this->safeRoleCount('teacher'),
                'staff_count'    => Staff::count(), // 💡 إجمالي الموظفين من جدول الـ Staff مباشرة
                'classes_count'  => ClassRoom::count(),
            ],
            'finance' => $this->getFinanceSummary(),
            'attendance' => $this->getTodayAttendanceSummary(),
            'students_by_stage' => $this->getStudentsByStage(),
            'staff_by_type' => $this->getStaffByType(),
            'activities' => $this->getRecentActivities(5),
            'notifications' => $this->getUnreadNotificationsForAuthUser(),
        ];
    }

    /**
     * 🧑‍🏫 2. إحصائيات الموجه (Adviser Dashboard)
     */
    public function getAdviserDashboard(): array
    {
        $today = Carbon::today()->toDateString();
        $user = Auth::user();

        $classRoomIds = [];
        $gradeLevelIds = [];

        $currentYear = \App\Models\AcademicYear::where('is_current', true)->first();

        if ($currentYear && $user) {
            $gradeLevelIds = \App\Models\GradeConfiguration::where('academic_year_id', $currentYear->id)
                ->where('supervisor_id', $user->id)
                ->pluck('grade_level_id');

            $classRoomIds = ClassRoom::whereIn('grade_level_id', $gradeLevelIds)->pluck('id');
        }

        $classesData = ClassRoom::whereIn('id', $classRoomIds)
            ->withCount(['enrollments as students_count' => function ($q) {
                $q->where('enrollment_status', 'enrolled');
            }])
            ->get()
            ->map(fn($c) => [
                'class_id'       => $c->id,
                'class_name'     => $c->name,
                'students_count' => $c->students_count,
            ])->toArray();

        $studentsCount = \App\Models\Enrollment::whereIn('class_room_id', $classRoomIds)
            ->where('enrollment_status', 'enrolled')
            ->count();

        $studentsWithAbsence = StudentAttendance::whereDate('attendance_date', $today)
            ->whereIn('status', ['absent', 'partial_absence'])
            ->whereHas('enrollment', fn($q) => $q->whereIn('class_room_id', $classRoomIds))
            ->count();

        $studentsWithUnexcusedAbsence = StudentAttendance::whereDate('attendance_date', $today)
            ->where('absence_type', 'unexcused')
            ->whereHas('enrollment', fn($q) => $q->whereIn('class_room_id', $classRoomIds))
            ->count();

        $presentCount = StudentAttendance::whereDate('attendance_date', $today)
            ->where('status', 'present')
            ->whereHas('enrollment', fn($q) => $q->whereIn('class_room_id', $classRoomIds))
            ->count();

        $excusedCount = StudentAttendance::whereDate('attendance_date', $today)
            ->where('absence_type', 'excused')
            ->whereHas('enrollment', fn($q) => $q->whereIn('class_room_id', $classRoomIds))
            ->count();

        return [
            'overview' => [
                'students_count'                  => $studentsCount,
                'classes_count'                   => count($classesData),
                'students_with_absence'           => $studentsWithAbsence,
                'students_with_unexcused_absence' => $studentsWithUnexcusedAbsence,
            ],
            'attendance' => [
                'present'           => $presentCount,
                'excused_absence'   => $excusedCount,
                'unexcused_absence' => $studentsWithUnexcusedAbsence,
            ],
            'students_by_stage' => $this->getStudentsByStageForGradeLevels($gradeLevelIds),
            'classes'           => $classesData,
            'activities'        => $this->getRecentActivities(5),
            'notifications'     => $this->getUnreadNotificationsForAuthUser(),
        ];
    }

    /**
     * 💼 3. إحصائيات السكرتير (Secretary Dashboard)
     */
    public function getSecretaryDashboard(): array
    {
        return [
            'overview' => [
                'students_count' => Student::count(),
                'teachers_count' => $this->safeRoleCount('teacher'),
                'staff_count'    => Staff::count(),
                'classes_count'  => ClassRoom::count(),
            ],
            'attendance' => $this->getTodayAttendanceSummary(),
            'finance' => $this->getFinanceSummary(),
            'students_by_stage' => $this->getStudentsByStage(),
            'activities' => $this->getRecentActivities(5),
            'notifications' => $this->getUnreadNotificationsForAuthUser(),
        ];
    }

    // ==========================================
    // 🛠️ دوال مساعدة لتقسيم المنطق (Helper Methods)
    // ==========================================

    /**
     * 🛡️ فحص الدور بأمان تام لمنع أي استثناءات
     */
    private function safeRoleCount(string $roleName): int
    {
        try {
            if (Role::where('name', $roleName)->where('guard_name', 'sanctum')->exists()) {
                return Staff::whereHas('user', fn($q) => $q->role($roleName))->count();
            }
        } catch (\Throwable $e) {
            // صيد الخطأ بصمت
        }
        return 0;
    }

    private function getFinanceSummary(): array
    {
        $totalDue = ScheduledInstallment::sum('amount_due') ?? 0;
        $totalPaid = PaymentTransaction::sum('paid_amount') ?? 0;
        $totalRemaining = max(0, $totalDue - $totalPaid);

        return [
            'total_due'       => (float) $totalDue,
            'total_paid'      => (float) $totalPaid,
            'total_remaining' => (float) $totalRemaining,
        ];
    }

    private function getTodayAttendanceSummary(): array
    {
        $today = Carbon::today()->toDateString();
        
        return [
            'present'           => StudentAttendance::whereDate('attendance_date', $today)->where('status', 'present')->count(),
            'excused_absence'   => StudentAttendance::whereDate('attendance_date', $today)->where('absence_type', 'excused')->count(),
            'unexcused_absence' => StudentAttendance::whereDate('attendance_date', $today)->where('absence_type', 'unexcused')->count(),
        ];
    }

    private function getStudentsByStage(): array
    {
        return AcademicStage::all()->map(function ($stage) {
            $gradeLevelIds = $stage->gradeLevels()->pluck('id');
            $studentsCount = \App\Models\Enrollment::whereIn('grade_level_id', $gradeLevelIds)
                ->where('enrollment_status', 'enrolled')
                ->count();

            return [
                'stage_id'       => $stage->id,
                'stage_name'     => $stage->name,
                'students_count' => $studentsCount,
            ];
        })->toArray();
    }

    private function getStudentsByStageForGradeLevels($gradeLevelIds): array
    {
        return AcademicStage::whereHas('gradeLevels', function($q) use ($gradeLevelIds) {
            $q->whereIn('id', $gradeLevelIds);
        })
        ->get()
        ->map(function ($stage) use ($gradeLevelIds) {
            $matchedGradeIds = $stage->gradeLevels()->whereIn('id', $gradeLevelIds)->pluck('id');
            $studentsCount = \App\Models\Enrollment::whereIn('grade_level_id', $matchedGradeIds)
                ->where('enrollment_status', 'enrolled')
                ->count();

            return [
                'stage_id'       => $stage->id,
                'stage_name'     => $stage->name,
                'students_count' => $studentsCount,
            ];
        })->toArray();
    }

    private function getStaffByType(): array
    {
        $rolesMap = [
            'teachers'      => 'teacher',       // المعلمون
            'advisers'      => 'adviser',       // الموجهون
            'secretaries'   => 'secretary',     // أمناء السر
            'counselors'    => 'counselor',     // المرشد النفسي
            'service_staff' => 'service',       // موظفو الخدمات
        ];

        $labels = [
            'teachers'      => 'المعلمون',
            'advisers'      => 'الموجهون',
            'secretaries'   => 'أمناء السر',
            'counselors'    => 'المرشد النفسي',
            'service_staff' => 'موظفو الخدمات',
        ];

        $result = [];
        foreach ($rolesMap as $type => $roleName) {
            $result[] = [
                'type'  => $type,
                'label' => $labels[$type],
                'count' => $this->safeRoleCount($roleName),
            ];
        }

        return $result;
    }

    private function getRecentActivities(int $limit = 5): array
    {
        return Alert::latest()->take($limit)->get()->map(fn($alert) => [
            'id'          => $alert->id,
            'type'        => $alert->type,
            'title'       => $alert->title,
            'description' => $alert->description,
            'created_at'  => $alert->created_at->toIso8601String(),
        ])->toArray();
    }

    private function getUnreadNotificationsForAuthUser(): array
    {
        $user = Auth::user();

        if (!$user) {
            return ['unread_count' => 0, 'items' => []];
        }

        $unreadQuery = Alert::whereDoesntHave('readers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        if ($user->hasRole('student') && $user->student) {
            $enrollmentIds = $user->student->enrollments()->pluck('id');
            $unreadQuery->where('notifiable_type', \App\Models\Enrollment::class)->whereIn('notifiable_id', $enrollmentIds);
        } elseif ($user->staff) {
            $unreadQuery->where('notifiable_type', Staff::class)->where('notifiable_id', $user->staff->id);
        }

        return [
            'unread_count' => $unreadQuery->count(),
            'items'        => $unreadQuery->latest()->take(5)->get()->map(fn($n) => [
                'id'      => $n->id,
                'type'    => $n->type,
                'title'   => $n->title,
                'message' => $n->description,
            ])->toArray(),
        ];
    }
    /**
     * 🧠 الدالة الموحدة: تكتشف دور المستخدم الحالي وتجلب الداشبورد المناسبة له تلقائياً
     */
    public function getDashboardForAuthUser(\App\Models\User $user): array
    {
        // 1. إذا كان مدير عام
        if ($user->hasRole('super_admin')) {
            return [
                'role' => 'super_admin',
                'dashboard_data' => $this->getSuperAdminDashboard()
            ];
        }

        // 2. إذا كان مووجهاً
        if ($user->hasRole('adviser')) {
            return [
                'role' => 'adviser',
                'dashboard_data' => $this->getAdviserDashboard()
            ];
        }

        // 3. إذا كان أمين سر / سكرتير
        if ($user->hasRole('secretary')) {
            return [
                'role' => 'secretary',
                'dashboard_data' => $this->getSecretaryDashboard()
            ];
        }

        // 4. خيار افتراضي في حال كان الموظف له دور آخر
        throw new \Exception('لا تتوفر لوحة تحكم مخصصة للدور الأكاديمي الخاص بحسابك.', 403);
    }
}