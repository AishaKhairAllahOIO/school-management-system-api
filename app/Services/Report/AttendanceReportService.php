<?php

namespace App\Services\Report;

use App\Models\StudentAttendance;
use App\Models\StaffAttendance;
use App\Models\TeacherPeriodAttendance;
use App\Models\Student;
use App\Models\Staff;
use App\Models\ClassRoom;
use App\Models\StudentAttendanceSetting;

class AttendanceReportService
{
    /**
     * 🎓 تقرير غياب وحضور الطلاب المجمل للمدرسة (بناءً على معمارية عدم تسجيل الحاضرين)
     */
  public function getOverallStudentsAttendanceReport(): array
    {
        $totalStudents = Student::count();

        $attendanceSetting = StudentAttendanceSetting::first();
        $workingDaysPerStudent = $attendanceSetting ? $attendanceSetting->working_days : 30;
        $totalExpectedAttendanceDays = $totalStudents * $workingDaysPerStudent;

        $allAbsences = StudentAttendance::all();
        $totalUnexcused = $allAbsences->where('absence_type', 'unexcused')->count();
        $totalExcused   = $allAbsences->where('absence_type', 'excused')->count();
        $totalAbsencesCount = $allAbsences->count();

        $totalPresentDays = max(0, $totalExpectedAttendanceDays - $totalAbsencesCount);

        $overallAttendanceRate = $totalExpectedAttendanceDays > 0 
            ? round(($totalPresentDays / $totalExpectedAttendanceDays) * 100, 1) 
            : 0;

        $overallAbsenceRate = $totalExpectedAttendanceDays > 0 
            ? round(($totalAbsencesCount / $totalExpectedAttendanceDays) * 100, 1) 
            : 0;

        $classRooms = ClassRoom::with('enrollments')->get();
        $classroomsSummary = [];

        foreach ($classRooms as $classRoom) {
            $studentCount = $classRoom->enrollments->count();

            if ($studentCount === 0) {
                $classroomsSummary[] = [
                    'class_room_id'      => $classRoom->id,
                    'class_room_name'    => $classRoom->name,
                    'student_count'      => 0,
                    'attendance_rate'    => 0,
                    'absence_rate'       => 0,
                    'unexcused_absences' => 0,
                    'excused_absences'   => 0,
                ];
                continue;
            }

            $classExpectedDays = $studentCount * $workingDaysPerStudent;
            
            // التحويل المباشر للاستعلام عبر نمط البيانات اللحظية (Snapshot)
            $classAbsences = StudentAttendance::where('class_room_id', $classRoom->id)->get();
            
            $classUnexcused = $classAbsences->where('absence_type', 'unexcused')->count();
            $classExcused   = $classAbsences->where('absence_type', 'excused')->count();
            $classTotalAbsent = $classAbsences->count();

            $classPresentDays = max(0, $classExpectedDays - $classTotalAbsent);

            $classAttendanceRate = $classExpectedDays > 0 
                ? round(($classPresentDays / $classExpectedDays) * 100, 1) 
                : 0;
                
            $classAbsenceRate = $classExpectedDays > 0 
                ? round(($classTotalAbsent / $classExpectedDays) * 100, 1) 
                : 0;

            $classroomsSummary[] = [
                'class_room_id'      => $classRoom->id,
                'class_room_name'    => $classRoom->name,
                'student_count'      => $studentCount,
                'attendance_rate'    => $classAttendanceRate,
                'absence_rate'       => $classAbsenceRate,
                'unexcused_absences' => $classUnexcused,
                'excused_absences'   => $classExcused,
            ];
        }

        return [
            'total_students'           => $totalStudents,
            'overall_attendance_rate'  => $overallAttendanceRate,
            'overall_absence_rate'     => $overallAbsenceRate,
            'total_unexcused_absences' => $totalUnexcused,
            'total_excused_absences'   => $totalExcused,
            'classrooms_summary'       => $classroomsSummary,
        ];
    }

    /**
     * 👨‍🏫 تقرير غياب وحضور الموظفين والمعلمين المجمل للمدرسة
     */
  public function getOverallStaffAttendanceReport(): array
{
    $totalStaff = Staff::count();

    $allStaffAttendances = StaffAttendance::all();

    $unexcusedDays = $allStaffAttendances->whereIn('status', ['absent', 'partial_absence'])->where('absence_type', 'unexcused')->count();
    $excusedDays   = $allStaffAttendances->whereIn('status', ['absent', 'partial_absence'])->where('absence_type', 'excused')->count();
    $leaveDays     = $allStaffAttendances->where('status', 'on_leave')->count();

    $totalAbsentDays = $unexcusedDays + $excusedDays + $leaveDays;

    $workingDaysPerStaff = 30;
    $totalExpectedStaffDays = $totalStaff * $workingDaysPerStaff;

    $totalPresentDays = max(0, $totalExpectedStaffDays - $totalAbsentDays);

    $overallAttendanceRate = $totalExpectedStaffDays > 0 ? round(($totalPresentDays / $totalExpectedStaffDays) * 100, 1) : 0;
    $overallAbsenceRate    = $totalExpectedStaffDays > 0 ? round(($totalAbsentDays / $totalExpectedStaffDays) * 100, 1) : 0;

    // 1. إجمالي عدد الحصص الفائتة الكلي في المدرسة
    $totalMissedPeriods = TeacherPeriodAttendance::count();

    // 2. تجميع الحصص الفائتة مقسمة حسب المادة الدراسية بالمسار الصحيح للعلاقات
    $missedPeriodsBySubject = TeacherPeriodAttendance::with('scheduleEntry.gradeSubject.subject')
        ->get()
        ->groupBy(function ($item) {
            // المسار الصحيح: scheduleEntry -> gradeSubject -> subject -> subject_name
            return $item->scheduleEntry?->gradeSubject?->subject?->subject_name ?? 'Unspecified Subject';
        })
        ->map(function ($items, $subjectName) {
            return [
                'subject_name'         => $subjectName,
                'missed_periods_count' => $items->count(),
            ];
        })
        ->values()
        ->toArray();

    return [
        'total_staff'                => $totalStaff,
        'overall_attendance_rate'    => $overallAttendanceRate,
        'overall_absence_rate'       => $overallAbsenceRate,
        'total_unexcused_days'       => $unexcusedDays,
        'total_excused_days'         => $excusedDays,
        'total_leave_days'           => $leaveDays,
        'total_missed_periods_count' => $totalMissedPeriods, 
        'missed_periods_by_subject'  => $missedPeriodsBySubject, 
    ];
}
}