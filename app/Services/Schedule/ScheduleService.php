<?php

namespace App\Services\Schedule;

use App\Models\Schedule;
use App\Models\ScheduleEntry;
use App\Models\AcademicSetting;
use App\Services\ScheduleValidator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ScheduleService
{
    public function __construct(
        private PeriodTimeCalculator $timeCalculator,
        private ScheduleValidator $validator
    ) {}

    public function checkBeforeGeneration(int $academicYearId, int $semesterId): void
    {
        $exists = Schedule::where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $semesterId)
            ->exists();

        if ($exists) {
            throw new Exception("A schedule already exists for this term. Please use regenerate instead.");
        }
    }

    public function deleteExistingSchedule(int $academicYearId, int $semesterId): void
    {
        DB::transaction(function () use ($academicYearId, $semesterId) {
            $schedule = Schedule::where('academic_year_id', $academicYearId)
                ->where('academic_term_id', $semesterId)
                ->first();

            if ($schedule) {
                $schedule->entries()->delete();
                $schedule->delete();
            }
        });
    }

    public function updateEntry(int $entryId, array $data): ScheduleEntry
    {
        $entry = ScheduleEntry::findOrFail($entryId);
        $entry->update($data);
        return $entry;
    }

    public function getAdminSchedule(int $scheduleId): array
    {
        $schedule = Schedule::with([
            'entries.classRoom.gradeLevel',
            'entries.gradeSubject.subject',
            'entries.teacher.user'
        ])->findOrFail($scheduleId);

        $report = $this->validator->validate($schedule);

        $settings = AcademicSetting::firstOrFail()->schedule_settings;
        $classesTree = [];

        foreach ($schedule->entries as $entry) {
            $gradeName = $entry->classRoom->gradeLevel->name->value ?? 'Unknown Grade';
            $roomName = $entry->classRoom->name;
            $day = strtolower($entry->day);

            $times = $this->timeCalculator->calculate($entry->period_index, $settings);

            $classesTree[$gradeName . ' - ' . $roomName][$day][] = [
                'period_index' => $entry->period_index,
                'subject_name' => $entry->gradeSubject->subject->subject_name ?? null,
                'teacher_name' => $entry->user->first_name ?? null,
                'is_heavy'     => $entry->gradeSubject->difficulty === 'heavy',
                'start_time'   => $times['start_time'],
                'end_time'     => $times['end_time'],
            ];
        }

        $daysOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

        foreach ($classesTree as $className => &$days) {
            uksort($days, fn($a, $b) => array_search($a, $daysOrder) <=> array_search($b, $daysOrder));

            foreach ($days as $day => &$periods) {
                usort($periods, fn($a, $b) => $a['period_index'] <=> $b['period_index']);
            }
        }

        return [
            'quality_report' => [
                'is_perfect' => $report['valid'],
                'hard_conflicts' => [
                    'teacher' => $report['statistics']['teacher_conflicts'],
                    'class'   => $report['statistics']['class_conflicts']
                ],
                'soft_violations_summary' => collect($report['errors'])->groupBy('type')->map->count()
            ],
            'classes' => $classesTree
        ];
    }

    public function getStudentWeeklySchedule(int $classroomId): array
    {
        $entries = ScheduleEntry::with(['gradeSubject.subject', 'teacher.user'])
            ->where('class_room_id', $classroomId)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }

    public function getStudentTomorrowSchedule(int $classroomId): array
    {
        $tomorrowDayString = $this->getSyrianTomorrowDayString();
        if (!$tomorrowDayString) return [];

        $entries = ScheduleEntry::with(['gradeSubject.subject', 'teacher.user'])
            ->where('class_room_id', $classroomId)
            ->where('day', $tomorrowDayString)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }

    public function getTeacherWeeklySchedule(int $teacherId): array
    {
        $entries = ScheduleEntry::with(['gradeSubject.subject', 'classRoom.gradeLevel'])
            ->where('teacher_id', $teacherId)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }

    public function getTeacherTomorrowSchedule(int $teacherId): array
    {
        $tomorrowDayString = $this->getSyrianTomorrowDayString();
        if (!$tomorrowDayString) return [];

        $entries = ScheduleEntry::with(['gradeSubject.subject', 'classRoom.gradeLevel'])
            ->where('teacher_id', $teacherId)
            ->where('day', $tomorrowDayString)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }

    private function getSyrianTomorrowDayString(): ?string
    {
        $tomorrow = Carbon::tomorrow();
        if ($tomorrow->isFriday() || $tomorrow->isSaturday()) {
            return null;
        }
        return strtolower($tomorrow->englishDayOfWeek);
    }

    private function formatEntriesWithTimes($entries): array
    {
        $settings = AcademicSetting::firstOrFail()->schedule_settings;
        $formatted = [];
        $daysOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

        foreach ($entries as $entry) {
            $times = $this->timeCalculator->calculate($entry->period_index, $settings);

            $formatted[strtolower($entry->day)][] = [
                'period_index' => $entry->period_index,
                'subject_name' => $entry->gradeSubject->subject->name ?? 'N/A',
                'teacher_name' => $entry->teacher->user->first_name ?? 'N/A',
                'classroom'    => $entry->classRoom->name ?? 'N/A',
                'start_time'   => $times['start_time'],
                'end_time'     => $times['end_time'],
            ];
        }

        uksort($formatted, fn($a, $b) => array_search($a, $daysOrder) <=> array_search($b, $daysOrder));

        foreach ($formatted as &$periods) {
            usort($periods, fn($a, $b) => $a['period_index'] <=> $b['period_index']);
        }

        return $formatted;
    }


    public function getAllTeachersSchedule(int $scheduleId): array
    {
        $schedule = Schedule::with([
            'entries.teacher.user',
            'entries.gradeSubject.subject',
            'entries.classRoom.gradeLevel'
        ])->findOrFail($scheduleId);

        $settings = AcademicSetting::firstOrFail()->schedule_settings;
        $teachersTree = [];

        foreach ($schedule->entries as $entry) {
            if (!$entry->teacher) continue;

            $teacherName = $entry->teacher->user->first_name ?? $entry->teacher->user->name ?? 'Teacher ' . $entry->teacher_id;
            $day = strtolower($entry->day);

            $times = $this->timeCalculator->calculate($entry->period_index, $settings);

            $gradeName = $entry->classRoom->gradeLevel->name->value ?? 'Unknown Grade';
            $roomName = $entry->classRoom->name ?? 'Unknown Room';

            $teachersTree[$teacherName][$day][] = [
                'entry_id'     => $entry->id,
                'period_index' => $entry->period_index,
                'subject_name' => $entry->gradeSubject->subject->name ?? 'N/A',
                'classroom'    => $gradeName . ' - ' . $roomName,
                'is_heavy'     => $entry->gradeSubject->difficulty === 'heavy',
                'start_time'   => $times['start_time'],
                'end_time'     => $times['end_time'],
            ];
        }

        $daysOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

        foreach ($teachersTree as $teacherName => &$days) {

        uksort($days, fn($a, $b) => array_search($a, $daysOrder) <=> array_search($b, $daysOrder));

            foreach ($days as $day => &$periods) {
                usort($periods, fn($a, $b) => $a['period_index'] <=> $b['period_index']);
            }
        }
        ksort($teachersTree);

        return $teachersTree;
    }
}
