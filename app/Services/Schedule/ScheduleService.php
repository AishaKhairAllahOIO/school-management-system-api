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
    ) {
    }

    public function checkBeforeGeneration(int $academicYearId, int $semesterId): void
    {
        $setting = AcademicSetting::first();

        if (!$setting || $setting->current_academic_year_id != $academicYearId || $setting->current_semester_id != $semesterId) {
            throw new Exception("Generation is only allowed for the currently active academic year and semester.");
        }

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
    public function getAdminSchedule(int $academicYearId, int $semesterId): array
    {


        $schedule = Schedule::with([
            'entries.classRoom.gradeLevel',
            'entries.gradeSubject.subject',
            'entries.gradeSubject.gradeLevel',
            'entries.teacher.user'
        ])
            ->where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $semesterId)
            ->firstOrFail();

        $report = $this->validator->validate($schedule);
        $settings = AcademicSetting::firstOrFail()->schedule_settings;

        $classesMap = [];

        foreach ($schedule->entries as $entry) {
            $classId = $entry->class_room_id;
            $gradeName = $entry->classRoom?->gradeLevel?->name?->value ?? 'Unknown Grade';
            $roomName = $entry->classRoom?->name ?? 'Unknown Room';
            $day = strtolower($entry->day);

            $times = $this->timeCalculator->calculate($entry->period_index, $settings);

            if (!isset($classesMap[$classId])) {
                $classesMap[$classId] = [
                    'grade_name' => $gradeName,
                    'class_room_name' => $roomName,
                    'schedule' => []
                ];
            }

            $classesMap[$classId]['schedule'][$day][] = [
                'entry_id' => $entry->id,
                'period_index' => $entry->period_index,

                'subject_name' =>
                    $entry->gradeSubject?->subject?->subject_name
                    ?? 'Deleted Subject',

                'teacher_name' =>
                    trim(
                        ($entry->teacher?->user?->first_name ?? '')
                        . ' ' .
                        ($entry->teacher?->user?->last_name ?? '')
                    ) ?: null,

                'is_heavy' =>
                    $entry->gradeSubject?->difficulty === 'heavy',

                'start_time' => $times['start_time'],
                'end_time' => $times['end_time'],
            ];
        }

        $daysOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

        foreach ($classesMap as &$classData) {
            uksort($classData['schedule'], fn($a, $b) => array_search($a, $daysOrder) <=> array_search($b, $daysOrder));
            foreach ($classData['schedule'] as $day => &$periods) {
                usort($periods, fn($a, $b) => $a['period_index'] <=> $b['period_index']);
            }
        }

        $violations = $report['errors'] ?? [];

        foreach ($violations as &$violation) {
            $errorClassId = $violation['class'] ?? $violation['class_room_id'] ?? null;
            if ($errorClassId && isset($classesMap[$errorClassId])) {
                $violation['grade_name'] = $classesMap[$errorClassId]['grade_name'];
                $violation['class_room_name'] = $classesMap[$errorClassId]['class_room_name'];
            }
        }
        unset($violation);

        $classesTree = array_values($classesMap);

        return [
            'id' => $schedule->id,
            'is_perfect' => $report['valid'],
            'quality_report' => [
                'statistics' => [
                    'entries' => $report['statistics']['entries'] ?? 0,
                    'teacher_conflicts' => $report['statistics']['teacher_conflicts'] ?? 0,
                    'class_conflicts' => $report['statistics']['class_conflicts'] ?? 0,
                ],
                'violations' => $violations
            ],
            'classes' => $classesTree
        ];
    }
    public function getStudentWeeklySchedule(int $classroomId): array
    {
        $entries = ScheduleEntry::with([
            'gradeSubject.subject',
            'gradeSubject.gradeLevel',
            'teacher.user',
            'classRoom'
        ])
            ->where('class_room_id', $classroomId)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }
    public function getStudentTomorrowSchedule(int $classroomId): array
    {
        $tomorrowDayString = $this->getSyrianTomorrowDayString();

        if (!$tomorrowDayString) {
            return [];
        }

        $entries = ScheduleEntry::with([
            'gradeSubject.subject',
            'gradeSubject.gradeLevel',
            'classRoom.gradeLevel',
            'teacher.user'
        ])
            ->where('class_room_id', $classroomId)
            ->where('day', $tomorrowDayString)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }
    public function getTeacherWeeklySchedule(int $teacherId): array
    {
        $entries = ScheduleEntry::with([
            'gradeSubject.subject',
            'gradeSubject.gradeLevel',
            'classRoom.gradeLevel',
            'teacher.user'
        ])
            ->where('teacher_id', $teacherId)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }
    public function getTeacherTomorrowSchedule(int $teacherId): array
    {
        $tomorrowDayString = $this->getSyrianTomorrowDayString();
        if (!$tomorrowDayString)
            return [];

        $entries = ScheduleEntry::with(['gradeSubject.subject', 'classRoom.gradeLevel', 'teacher.user'])
            ->where('teacher_id', $teacherId)
            ->where('day', $tomorrowDayString)
            ->get();

        return $this->formatEntriesWithTimes($entries);
    }
    private function getSyrianTomorrowDayString(): ?string
    {
        $tomorrow = Carbon::tomorrow();

        if ($tomorrow->isFriday() || $tomorrow->isSaturday() || $tomorrow->isThursday()) {
            return 'sunday';
        }

        return strtolower($tomorrow->englishDayOfWeek);
    }
    private function formatEntriesWithTimes($entries): array
    {
        $settings = AcademicSetting::firstOrFail()->schedule_settings;

        $formatted = [];
        $daysOrder = [
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday'
        ];

        foreach ($entries as $entry) {

            $times = $this->timeCalculator->calculate(
                $entry->period_index,
                $settings
            );

            $formatted[strtolower($entry->day)][] = [
                'period_index' => $entry->period_index,

                'subject_name' =>
                    $entry->gradeSubject?->subject?->subject_name ?? null,

                'grade_name' =>
                    $entry->gradeSubject?->gradeLevel?->name?->value ?? null,

                'classroom' =>
                    $entry->classRoom?->name ?? null,

                'teacher_name' => $entry->teacher
                    ? (
                        ($entry->teacher->user?->first_name ?? '')
                        . ' ' .
                        ($entry->teacher->user?->last_name ?? '')
                    )
                    : null,

                'start_time' => $times['start_time'],
                'end_time' => $times['end_time'],
            ];
        }


        uksort(
            $formatted,
            fn($a, $b) =>
            array_search($a, $daysOrder)
            <=>
            array_search($b, $daysOrder)
        );


        foreach ($formatted as &$periods) {
            usort(
                $periods,
                fn($a, $b) =>
                $a['period_index'] <=> $b['period_index']
            );
        }

        return $formatted;
    }
    public function getAllTeachersSchedule(int $academicYearId, int $semesterId): array
    {
        $schedule = Schedule::with([
            'entries.teacher.user',
            'entries.gradeSubject.subject',
            'entries.gradeSubject.gradeLevel',
            'entries.classRoom.gradeLevel'
        ])
            ->where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $semesterId)
            ->firstOrFail();

        $settings = AcademicSetting::firstOrFail()->schedule_settings;
        $teachersTree = [];

        foreach ($schedule->entries as $entry) {
            if (!$entry->teacher)
                continue;

            $teacherName = $entry->teacher->user->first_name . ' ' . $entry->teacher->user->last_name ?? $entry->teacher->user->name ?? 'Teacher ' . $entry->teacher_id;
            $day = strtolower($entry->day);

            $times = $this->timeCalculator->calculate($entry->period_index, $settings);

            $gradeName = $entry->classRoom?->gradeLevel?->name?->value ?? 'Unknown Grade';
            $roomName = $entry->classRoom?->name ?? 'Unknown Room';

            $teachersTree[$teacherName][$day][] = [
                'entry_id' => $entry->id,
                'period_index' => $entry->period_index,
                'subject_name' => $entry->gradeSubject->subject->subject_name ?? null,
                'grade_name' => $gradeName,
                'classroom' => $roomName,
                'is_heavy' => $entry->gradeSubject->difficulty === 'heavy',
                'start_time' => $times['start_time'],
                'end_time' => $times['end_time'],
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
    public function updateEntry(int $entryId, array $data): ScheduleEntry
    {
        $entry = ScheduleEntry::find($entryId);

        $entry->update($data);

        if (isset($data['day']) && isset($data['period_index'])) {
            $this->syncDayPeriodsCount($data['day'], $data['period_index']);
        } elseif (isset($data['period_index'])) {
            $this->syncDayPeriodsCount($entry->day, $data['period_index']);
        }

        return $entry;
    }
    public function addEntry(array $data): ScheduleEntry
    {
        $setting = AcademicSetting::firstOrFail();
        $scheduleSettings = $setting->schedule_settings ?? [];
        $workingDays = $scheduleSettings['workingDays'] ?? [];

        $currentPeriodsCount = 0;

        foreach ($workingDays as $workingDay) {
            if (strtolower($workingDay['day']) === strtolower($data['day'])) {
                $currentPeriodsCount = (int) $workingDay['periodsCount'];
                break;
            }
        }

        $newPeriodIndex = $currentPeriodsCount + 1;

        if ($newPeriodIndex > 9) {
            throw new Exception(
                "You add sessions more than the allwoed number of sessions.",
                422
            );
        }


        $data['period_index'] = $newPeriodIndex;

        $exists = ScheduleEntry::where('schedule_id', $data['schedule_id'])
            ->where('class_room_id', $data['class_room_id'])
            ->where('day', $data['day'])
            ->where('period_index', $data['period_index'])
            ->exists();

        if ($exists) {
            throw new Exception(
                "This period already exists for {$data['day']}.",
                422
            );
        }

        $entry = ScheduleEntry::create($data);

        $this->syncDayPeriodsCount($data['day'], $data['period_index']);

        return $entry;
    }
    private function syncDayPeriodsCount(string $day, int $periodIndex): void
    {
        $setting = AcademicSetting::first();
        if (!$setting || empty($setting->schedule_settings))
            return;

        $scheduleSettings = $setting->schedule_settings;
        $updated = false;

        if (isset($scheduleSettings['workingDays'])) {
            foreach ($scheduleSettings['workingDays'] as &$workingDay) {
                if (strtolower($workingDay['day']) === strtolower($day)) {
                    if ($periodIndex > (int) $workingDay['periodsCount']) {
                        $workingDay['periodsCount'] = $periodIndex;
                        $updated = true;
                    }
                    break;
                }
            }
        }

        if ($updated) {
            $setting->schedule_settings = $scheduleSettings;
            $setting->save();
        }
    }


}
