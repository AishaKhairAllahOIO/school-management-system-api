<?php

namespace App\Domain\Scheduling\Persistence;

use App\Models\Schedule;
use App\Models\ScheduleEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // For handling timestamps automatically

class SchedulePersistenceService
{
    public function save(array $solution, int $yearId, int $termId): Schedule
    {
        return DB::transaction(function () use ($solution, $yearId, $termId) {

            $schedule = Schedule::create([
                'academic_year_id' => $yearId,
                'academic_term_id' => $termId,
                'status'           => 'generated',
                'score'            => 100 // Can be dynamic later if optimization is added
            ]);

            $entriesToInsert = [];
            $now = Carbon::now();

            // 1. Prepare data in memory (Fast)
            foreach ($solution as $item) {
                $entriesToInsert[] = [
                    'schedule_id'           => $schedule->id,
                    'teacher_assignment_id' => $item['lesson']['assignmentId'],
                    'teacher_id'            => $item['lesson']['teacherId'],
                    'class_room_id'         => $item['lesson']['classRoomId'],
                    'grade_subject_id'      => $item['lesson']['gradeSubjectId'],
                    'day'                   => $item['slot']['day'],
                    'period_index'          => $item['slot']['periodIndex'],
                    'source'                => 'generated',
                    'created_at'            => $now,  // Required for bulk insert
                    'updated_at'            => $now,  // Required for bulk insert
                ];
            }

            // 2. Bulk insert in chunks to prevent SQL size limit errors (Extremely Fast)
            // Inserting 500 records per query
            foreach (array_chunk($entriesToInsert, 500) as $chunk) {
                ScheduleEntry::insert($chunk);
            }

            return $schedule;
        });
    }
}
