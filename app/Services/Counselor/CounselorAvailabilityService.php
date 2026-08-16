<?php

namespace App\Services\Counselor;


use App\Models\CounselorAvailability;
use Illuminate\Support\Facades\DB;


class CounselorAvailabilityService
{


    public function saveSchedule(int $counselorId, array $schedule)
    {

        return DB::transaction(function () use ($counselorId, $schedule) {

            CounselorAvailability::where(
                'counselor_id',
                $counselorId
            )->delete();



            $data = [];


            foreach ($schedule as $item) {

                $data[] = [

                    'counselor_id' => $counselorId,

                    'day' => $item['day'],

                    'start_time' => $item['start_time'],

                    'end_time' => $item['end_time'],

                    'session_duration' => $item['session_duration'],

                    'daily_sessions_limit' => $item['daily_sessions_limit'],

                    'created_at' => now(),

                    'updated_at' => now(),

                ];

            }


            return CounselorAvailability::insert($data);

        });

    }

    public function getSchedule(int $counselorId)
    {

        return CounselorAvailability::where(
            'counselor_id',
            $counselorId
        )
            ->orderBy('day')
            ->get();

    }

    public function updateDay(int $counselorId, string $day, array $data)
    {

        $availability =
            CounselorAvailability::where('counselor_id', $counselorId)
                ->where('day', $day)
                ->firstOrFail();


        $availability->update($data);


        return $availability;

    }

    public function deleteDay(int $counselorId, string $day)
    {

        return CounselorAvailability::where('counselor_id', $counselorId)
            ->where('day', $day)
            ->delete();

    }


}