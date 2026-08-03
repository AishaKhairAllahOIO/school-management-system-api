<?php 
namespace App\Services\Student;
use App\Models\StudentAttendanceSetting;
use App\Models\StudentAttendance;
use Exception;

class StudentAttendanceSettingsService
{
    public function getAllSettings()
    {
        return StudentAttendanceSetting::with('semester')->get();
    }

    public function getSettingsBySemester(int $semesterId): ?StudentAttendanceSetting
    {
        return StudentAttendanceSetting::where('semester_id', $semesterId)->firstOrFail();
    }

    // الإنشاء فقط (Create)
    public function createSettings(array $data): StudentAttendanceSetting
    {
        return StudentAttendanceSetting::create([
            'semester_id' => $data['semester_id'],
            'working_days' => $data['working_days'],
            'required_attendance_percentage' => $data['required_attendance_percentage'],
        ]);
    }

    // التعديل فقط (Update) - يعتمد على الـ ID الخاص بالإعداد، وليس الفصل
    public function updateSettings(int $id, array $data): StudentAttendanceSetting
    {
        $setting = StudentAttendanceSetting::findOrFail($id);
        $hasAttendances = StudentAttendance::where('semester_id', $setting->semester_id)->exists();
        if($hasAttendances)
        {
            throw new Exception("لا يمكن تعديل الإعدادات. يوجد سجلات حضور فعلية للطلاب مرتبطة بهذا الفصل الدراسي.");
        }

        
        $setting->update($data);
        
        return $setting;
    }

    public function deleteSettings(int $id): void
    {
        $setting = StudentAttendanceSetting::findOrFail($id);
        
        $hasAttendances = StudentAttendance::where('semester_id', $setting->semester_id)->exists();
        
        if ($hasAttendances) {
            throw new Exception("لا يمكن حذف الإعدادات. يوجد سجلات حضور فعلية للطلاب مرتبطة بهذا الفصل الدراسي.");
        }

        $setting->delete();
    }
}