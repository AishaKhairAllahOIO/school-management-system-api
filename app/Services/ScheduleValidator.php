<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\TeacherAssignment;

class ScheduleValidator
{
    public function validate(Schedule $schedule): array
    {
        $schedule->load([
            'entries.teacher',
            'entries.classRoom',
            'entries.gradeSubject.subject'
        ]);

        $errors = [];

        $errors = array_merge(
            $errors,
            $this->checkTeacherConflicts($schedule),
            $this->checkClassConflicts($schedule),
            $this->checkSubjectPeriods($schedule),
            $this->checkDailySubjectLimit($schedule),
            $this->checkAssignments($schedule),
            $this->checkAssignmentPeriods($schedule),
            $this->checkSubjectPeriodRestrictions($schedule),
            $this->checkHeavySubjects($schedule),
            $this->checkTeacherDailyLoad($schedule),
            $this->validateSubjectTeacherConsistency($schedule->entries)

        );


        return [
            'valid' => empty($errors),

            'errors' => $errors,

            'statistics' => [
                'entries' => $schedule->entries->count(),

                'assignments_used' =>
                    $schedule->entries
                        ->pluck('teacher_assignment_id')
                        ->unique()
                        ->count(),

                'teacher_conflicts' =>
                    count(
                        $this->checkTeacherConflicts($schedule)
                    ),

                'class_conflicts' =>
                    count(
                        $this->checkClassConflicts($schedule)
                    ),
            ]
        ];
    }
    private function checkTeacherDailyLoad($schedule)
    {
        return $schedule->entries
            ->groupBy(
                fn($e) =>
                $e->teacher_id . '-' . $e->day
            )
            ->filter(
                fn($g) =>
                $g->count() > 5
            )
            ->map(fn($g) => [
                'type' => 'teacher_daily_overload',
                'teacher' => $g->first()->teacher_id,
                'day' => $g->first()->day,
                'count' => $g->count()
            ])
            ->values()
            ->toArray();
    }
    private function validateSubjectTeacherConsistency($entries)
    {
        $errors = [];

        $entries
            ->groupBy(fn($e) => $e->class_room_id . '-' . $e->gradeSubject->subject_id)
            ->filter(fn($group) => $group->pluck('teacher_id')->unique()->count() > 1)
            ->each(function ($group) use (&$errors) {

                $errors[] = [
                    'type' => 'subject_teacher_conflict',
                    'class_room_id' => $group->first()->class_room_id,
                    'subject_id' => $group->first()->gradeSubject->subject_id,
                    'teachers' => $group->pluck('teacher_id')->unique()->values(),
                ];

            });

        return $errors;
    }
    private function checkHeavySubjects($schedule)
    {
        return $schedule->entries
            ->groupBy(
                fn($e) =>
                $e->class_room_id . '-' . $e->day
            )
            ->filter(function ($group) {

                return $group
                    ->filter(
                        fn($e) =>
                        $e->gradeSubject->difficulty == 'heavy'
                    )
                    ->count() > 2;

            })
            ->map(fn($g) => [
                'type' => 'too_many_heavy_subjects',
                'class' => $g->first()->class_room_id,
                'day' => $g->first()->day
            ])
            ->values()
            ->toArray();
    }
    private function checkSubjectPeriodRestrictions($schedule)
    {
        $errors = [];

        $maxPeriodInDay = 8;

        foreach ($schedule->entries as $entry) {
            $subject = $entry->gradeSubject;

            if ($subject->avoid_first_period && $entry->period_index == 1) {
                $errors[] = [
                    'type' => 'avoid_first_period',
                    'subject' => $subject->id,
                    'day' => $entry->day,
                    'period' => $entry->period_index
                ];
            }

            if ($subject->avoid_last_period && $entry->period_index == $maxPeriodInDay) {
                $errors[] = [
                    'type' => 'avoid_last_period',
                    'subject' => $subject->id,
                    'day' => $entry->day,
                    'period' => $entry->period_index
                ];
            }
        }

        return $errors;
    }

    private function checkTeacherConflicts($schedule)
    {
        return $schedule->entries
            ->groupBy(
                fn($e) =>
                $e->day . '-' . $e->period_index . '-' . $e->teacher_id
            )
            ->filter(fn($g) => $g->count() > 1)
            ->map(fn($g) => [
                'type' => 'teacher_conflict',
                'teacher' => $g->first()->teacher_id,
                'day' => $g->first()->day,
                'period' => $g->first()->period_index
            ])
            ->values()
            ->toArray();
    }

    private function checkClassConflicts($schedule)
    {
        return $schedule->entries
            ->groupBy(
                fn($e) =>
                $e->day . '-' . $e->period_index . '-' . $e->class_room_id
            )
            ->filter(fn($g) => $g->count() > 1)
            ->map(fn($g) => [
                'type' => 'class_conflict',
                'class' => $g->first()->class_room_id,
                'day' => $g->first()->day,
                'period' => $g->first()->period_index
            ])
            ->values()
            ->toArray();
    }

private function checkSubjectPeriods($schedule)
    {
        $errors = [];

        // 1. التجميع يجب أن يكون حسب الشعبة والمادة معاً، وليس المادة فقط
        $subjects = $schedule->entries->groupBy(
            fn($e) => $e->class_room_id . '-' . $e->grade_subject_id
        );

        foreach ($subjects as $key => $entries) {
            $firstEntry = $entries->first();

            // جلب العدد المطلوب من المادة
            $required = $firstEntry->gradeSubject->weekly_periods;

            // حساب العدد الفعلي الذي تم توليده لهذه الشعبة في هذه المادة
            $generated = $entries->count();

            // 2. المقارنة وإضافة الخطأ في حال عدم التطابق
            if ($generated != $required) {
                $errors[] = [
                    'type' => 'subject_period_mismatch',
                    'class_room_id' => $firstEntry->class_room_id,
                    'grade_subject_id' => $firstEntry->grade_subject_id,
                    'expected' => $required,
                    'generated' => $generated
                ];
            }
        }

        return $errors;
    }


    private function checkDailySubjectLimit($schedule)
    {
        return $schedule->entries
            ->groupBy(
                fn($e) =>
                $e->grade_subject_id . '-' . $e->class_room_id . '-' . $e->day
            )
            ->filter(
                fn($g) =>
                $g->count()
                >
                $g->first()
                    ->gradeSubject
                    ->max_periods_per_day
            )
            ->map(fn($g) => [
                'type' => 'daily_subject_limit',
                'subject' => $g->first()->grade_subject_id,
                'class' => $g->first()->class_room_id,
                'day' => $g->first()->day,
                'count' => $g->count(),
                'limit' => $g->first()->gradeSubject->max_periods_per_day
            ])
            ->values()
            ->toArray();
    }
private function checkAssignmentPeriods($schedule)
    {
        $errors = [];

        // أضفنا with('gradeSubject') لجلب العلاقة
        $assignments = TeacherAssignment::with('gradeSubject')
            ->where('academic_year_id', $schedule->academic_year_id)
            ->where('semester_id', $schedule->academic_term_id)
            ->get();

        foreach ($assignments as $assignment) {
            $generated = $schedule->entries
                ->where('teacher_assignment_id', $assignment->id)
                ->count();

            // الإصلاح: جلب weekly_periods من العلاقة gradeSubject
            $expected = $assignment->gradeSubject->weekly_periods ?? 0;

            if ($generated != $expected) {
                $errors[] = [
                    'type' => 'assignment_period_mismatch',
                    'assignment' => $assignment->id,
                    'expected' => $expected,
                    'generated' => $generated
                ];
            }
        }

        return $errors;
    }
    private function checkAssignments($schedule)
    {
        $used = $schedule->entries->pluck('teacher_assignment_id')->unique()->count();

        // CRITICAL FIX: Count assignments ONLY for this specific schedule's Year and Term!
        $total = TeacherAssignment::where('academic_year_id', $schedule->academic_year_id)
            ->where('semester_id', $schedule->academic_term_id)
            ->count();

        if ($used != $total) {
            return [
                [
                    'type' => 'missing_assignments',
                    'used' => $used,
                    'total' => $total
                ]
            ];
        }

        return [];
    }
}
