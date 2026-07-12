<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicSetting;

class AcademicSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create or update an AcademicSetting record like these :
        /*        "currentAcademicYearId": "1",
        "currentSemesterId": "1",
        "scheduleSettings": {
            "dayStartTime": "08:00",
            "periodDurationMinutes": 45,
            "workingDays": [
                {
                    "day": "sunday",
                    "periodsCount": 7
                },
                {
                    "day": "monday",
                    "periodsCount": 7
                },
                {
                    "day": "tuesday",
                    "periodsCount": 7
                },
                {
                    "day": "wednesday",
                    "periodsCount": 7
                },
                {
                    "day": "thursday",
                    "periodsCount": 5
                }
            ],
            "breaks": [
                {
                    "id": "brk_1",
                    "afterPeriodIndex": 3,
                    "durationMinutes": 20
                },
                {
                    "id": "brk_2",
                    "afterPeriodIndex": 5,
                    "durationMinutes": 10
                }
            ]
        } */
       AcademicSetting::updateOrCreate(
            ['id' => 1],
            [
                'current_academic_year_id' => 1,
                'current_semester_id' => 1,
                'schedule_settings' => json_encode([
                    'dayStartTime' => '08:00',
                    'periodDurationMinutes' => 45,
                    'workingDays' => [
                        ['day' => 'sunday', 'periodsCount' => 7],
                        ['day' => 'monday', 'periodsCount' => 7],
                        ['day' => 'tuesday', 'periodsCount' => 7],
                        ['day' => 'wednesday', 'periodsCount' => 7],
                        ['day' => 'thursday', 'periodsCount' => 5],
                    ],
                    'breaks' => [
                        ['id' => 'brk_1', 'afterPeriodIndex' => 3, 'durationMinutes' => 20],
                        ['id' => 'brk_2', 'afterPeriodIndex' => 5, 'durationMinutes' => 10],
                    ],
                ]),
            ]
        );
            


    }
}
