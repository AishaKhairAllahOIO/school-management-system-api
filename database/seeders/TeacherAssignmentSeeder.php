<?php

namespace Database\Seeders;

use App\Models\TeacherAssignment;
use App\Models\Staff;
use App\Models\GradeSubject;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $mathId       = Staff::where('user_id', 3)->value('id');
        $physicsId    = Staff::where('user_id', 21)->value('id');
        $chemistryId  = Staff::where('user_id', 15)->value('id');
        $arabicId     = Staff::where('user_id', 19)->value('id');
        $islamicId    = Staff::where('user_id', 25)->value('id');
        $englishId    = Staff::where('user_id', 22)->value('id');
        $frenchId     = Staff::where('user_id', 16)->value('id');
        $biologyId    = Staff::where('user_id', 13)->value('id');
        $historyId    = Staff::where('user_id', 23)->value('id');
        $geoId        = Staff::where('user_id', 24)->value('id');
        $informaticsId= Staff::where('user_id', 26)->value('id');
        $peId         = Staff::where('user_id', 17)->value('id');
        $artsId       = Staff::where('user_id', 18)->value('id');
        $musicId      = Staff::where('user_id', 27)->value('id');

        $teacherMapping = [
            1  => [$mathId, $physicsId],    // الرياضيات (5 حصص) -> بديله الفيزياء
            2  => [$physicsId],             // الفيزياء
            3  => [$chemistryId],           // الكيمياء
            4  => [$arabicId, $islamicId],  // اللغة العربية (5 حصص) -> بديله التربية الإسلامية
            5  => [$englishId],             // اللغة الإنكليزية
            6  => [$frenchId],              // اللغة الفرنسية
            7  => [$biologyId],             // العلوم
            8  => [$historyId],             // التاريخ
            9  => [$geoId],                 // الجغرافيا
            10 => [$islamicId],             // التربية الإسلامية
            11 => [$informaticsId],         // المعلوماتية
            12 => [$peId],                  // الرياضة
            13 => [$artsId],                // الفنون
            14 => [$musicId],               // الموسيقى
        ];

        $teacherWorkload = [];
        $maxWeeklyPeriods = 35;

        $classRooms = [
            1 => [1, 2, 3, 4, 5],
            2 => [6, 7, 8],
        ];

        foreach ($classRooms as $gradeLevel => $classes) {
            foreach ($classes as $classId) {
                for ($baseSubject = 1; $baseSubject <= 14; $baseSubject++) {

                    $gradeSubjectId = ($gradeLevel == 1) ? $baseSubject : $baseSubject + 14;

                    $subject = GradeSubject::find($gradeSubjectId);
                    $periods = $subject ? $subject->weekly_periods : 2;

                    $assignedTeacherId = null;

                    foreach ($teacherMapping[$baseSubject] as $teacherId) {
                        if (!$teacherId) continue;

                        $currentLoad = $teacherWorkload[$teacherId] ?? 0;

                        if (($currentLoad + $periods) <= $maxWeeklyPeriods) {
                            $assignedTeacherId = $teacherId;
                            $teacherWorkload[$teacherId] = $currentLoad + $periods;
                            break;
                        }
                    }

                    if (!$assignedTeacherId) {
                        $allTeachers = [$mathId, $physicsId, $chemistryId, $arabicId, $islamicId, $englishId, $frenchId, $biologyId, $historyId, $geoId, $informaticsId, $peId, $artsId, $musicId];
                        foreach ($allTeachers as $fallbackId) {
                            if (!$fallbackId) continue;
                            $currentLoad = $teacherWorkload[$fallbackId] ?? 0;
                            if (($currentLoad + $periods) <= $maxWeeklyPeriods) {
                                $assignedTeacherId = $fallbackId;
                                $teacherWorkload[$fallbackId] = $currentLoad + $periods;
                                break;
                            }
                        }
                    }

                    if ($assignedTeacherId) {
                        TeacherAssignment::updateOrCreate(
                            [
                                'academic_year_id' => 1,
                                'semester_id' => 1,
                                'class_room_id' => $classId,
                                'grade_subject_id' => $gradeSubjectId
                            ],
                            ['teacher_id' => $assignedTeacherId]
                        );
                    }
                }
            }
        }
    }
}
