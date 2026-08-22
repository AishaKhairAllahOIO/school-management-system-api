<?php

namespace App\Services\Web;

use App\ApiResource;
use App\Jobs\SendPushNotification;
use App\Models\Activity;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\GradeConfiguration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ActivityService
{

    use ApiResource;
    public function addActivity(array $data)
    {
        $classRoomIds = $data['class_room_ids'] ?? [];
        $activityData = Arr::except($data, ['class_room_ids']);

        $activity = Activity::create($activityData);

        if (!empty($classRoomIds)) {
            $activity->classRooms()->attach($classRoomIds);
        }

        $enrollmentsQuery = Enrollment::where('grade_level_id', $activity->grade_level_id)
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            });

        if (!empty($classRoomIds)) {
            $enrollmentsQuery->whereIn('class_room_id', $classRoomIds);
        }

        $studentIds = $enrollmentsQuery->pluck('student_id');

        $usersIds = User::whereHas('student', function ($q) use ($studentIds) {
            $q->whereIn('id', $studentIds);
        })->orWhereHas('guardian', function ($q) use ($studentIds) {
            $q->whereHas('students', function ($sq) use ($studentIds) {
                $sq->whereIn('id', $studentIds);
            });
        })->pluck('id')->toArray();

        if (!empty($usersIds)) {
            SendPushNotification::dispatch(
                $usersIds,
                'نشاط مدرسي جديد',
                'تمت إضافة نشاط جديد: ' . $activity->activity_name,
                ['activity_id' => (string) $activity->id, 'type' => 'activity']
            );
        }

        return $activity->load(['gradeLevel:id,name', 'classRooms:id,name']);
    }
    public function showActivities(Student $student): LengthAwarePaginator
    {
        $enrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })->latest()->first();

        if (!$enrollment) {
            return new LengthAwarePaginator([], 0, 20);
        }

        return Activity::query()
            ->where('grade_level_id', $enrollment->grade_level_id)
            ->where(function ($query) use ($enrollment) {
                $query->doesntHave('classRooms')
                    ->orWhereHas('classRooms', function ($q) use ($enrollment) {
                        $q->where('class_rooms.id', $enrollment->class_room_id);
                    });
            })
            ->with(['gradeLevel:id,name', 'classRooms:id,name'])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->paginate(20);
    }
    public function updateActivity(Activity $activity, array $data)
    {
        $classRoomIds = $data['class_room_ids'] ?? null;
        $activityData = Arr::except($data, ['class_room_ids']);

        $activity->update($activityData);

        if ($classRoomIds !== null) {
            $activity->classRooms()->sync($classRoomIds);
        }


        $enrollmentsQuery = Enrollment::where(
            'grade_level_id',
            $activity->grade_level_id
        )
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            });


        if (!empty($classRoomIds)) {
            $enrollmentsQuery->whereIn('class_room_id', $classRoomIds);
        }


        $studentIds = $enrollmentsQuery
            ->pluck('student_id');


        $userIds = User::whereHas('student', function ($q) use ($studentIds) {
            $q->whereIn('id', $studentIds);

        })->orWhereHas('guardian', function ($q) use ($studentIds) {

            $q->whereHas('students', function ($sq) use ($studentIds) {
                $sq->whereIn('id', $studentIds);
            });

        })
            ->pluck('id')
            ->toArray();

        if (!empty($userIds)) {

            DB::table('activity_user')
                ->where('activity_id', $activity->id)
                ->whereIn('user_id', $userIds)
                ->delete();


            SendPushNotification::dispatch(
                $userIds,
                'تعديل نشاط مدرسي',
                'تم تعديل النشاط: ' . $activity->activity_name,
                [
                    'activity_id' => (string) $activity->id,
                    'type' => 'activity_update'
                ]
            );
        }


        return $activity->load([
            'gradeLevel:id,name',
            'classRooms:id,name'
        ]);
    }
    public function deleteActivity(int $id): void
    {
        $activity = Activity::find($id);

        if (!$activity)
            throw new HttpResponseException($this->errorResponse('Activity not found.', 404));
        $activity->delete();
    }
    private function getBaseQueryForUser(User $user, ?int $specificStudentId = null)
    {
        if ($user->student) {
            $enrollment = $user->student->enrollments()
                ->whereHas('academicYear', function ($q) {
                    $q->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now());
                })->latest()->first();

            if (!$enrollment)
                return Activity::query()->where('id', '<', 0);

            return Activity::query()
                ->where('grade_level_id', $enrollment->grade_level_id)
                ->where(function ($q) use ($enrollment) {
                    $q->doesntHave('classRooms')
                        ->orWhereHas('classRooms', function ($subQ) use ($enrollment) {
                            $subQ->where('class_rooms.id', $enrollment->class_room_id);
                        });
                });
        }

        if ($user->guardian) {
            $studentsQuery = $user->guardian->students();

            if ($specificStudentId) {
                $studentsQuery->where('students.id', $specificStudentId);
            }

            $studentIds = $studentsQuery->pluck('students.id')->toArray();

            $enrollments = Enrollment::whereIn('student_id', $studentIds)
                ->whereHas('academicYear', function ($q) {
                    $q->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now());
                })->get();

            if ($enrollments->isEmpty())
                return Activity::query()->where('id', '<', 0);

            return Activity::query()->where(function ($query) use ($enrollments) {
                foreach ($enrollments as $enrollment) {
                    $query->orWhere(function ($q) use ($enrollment) {
                        $q->where('grade_level_id', $enrollment->grade_level_id)
                            ->where(function ($subQ) use ($enrollment) {
                                $subQ->doesntHave('classRooms')
                                    ->orWhereHas('classRooms', function ($cq) use ($enrollment) {
                                        $cq->where('class_rooms.id', $enrollment->class_room_id);
                                    });
                            });
                    });
                }
            });
        }

        return Activity::query()->where('id', '<', 0);
    }

    private function resolveReaderUser(User $user, ?int $studentId): ?User
    {
        if ($user->student) {
            return $user;
        }

        if ($user->guardian && $studentId) {
            $child = $user->guardian->students()->find($studentId);
            return $child?->user;
        }

        return null;
    }

    public function unreadCount(User $user, ?int $studentId = null): int
    {
        if ($user->hasRole('guardian') && $user->guardian && !$studentId) {
            return $user->guardian->students->reduce(function ($carry, Student $child) use ($user) {
                return $carry + $this->unreadCount($user, $child->id);
            }, 0);
        }

        $readerUser = $this->resolveReaderUser($user, $studentId);

        return $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($readerUser) {
                $q->where('user_id', $readerUser?->id);
            })
            ->count();
    }
    public function markAllAsRead(User $user, ?int $studentId = null): void
    {
        if ($user->guardian && !$studentId) {
            foreach ($user->guardian->students as $child) {
                $this->markAllAsRead($user, $child->id);
            }
            return;
        }

        $readerUser = $this->resolveReaderUser($user, $studentId);

        if (!$readerUser) {
            return;
        }

        $activityIds = $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($readerUser) {
                $q->where('user_id', $readerUser->id);
            })
            ->pluck('id');

        $syncData = [];
        $now = now();
        foreach ($activityIds as $id) {
            $syncData[$id] = ['read_at' => $now];
        }

        if (!empty($syncData)) {
            $readerUser->readActivities()->syncWithoutDetaching($syncData);
        }
    }
    public function getAllActivities(User $user): LengthAwarePaginator
    {
        $query = Activity::query()->with(['gradeLevel:id,name', 'classRooms:id,name']);

        if ($user->hasRole('super_admin')) {
        } elseif ($user->hasRole('advisor')) {
            $advisorGradeIds = GradeConfiguration::where('supervisor_id', $user->id)
                ->whereHas('academicYear', function ($q) {
                    $q->where('is_current', true);
                })
                ->pluck('grade_level_id')
                ->toArray();

            $query->whereIn('grade_level_id', $advisorGradeIds);
        } elseif ($user->hasRole('teacher')) {
            $teacherClassRooms = $user->staff->teacherAssignments()
                ->pluck('class_room_id')
                ->toArray();

            $teacherGrades = ClassRoom::whereIn('id', $teacherClassRooms)
                ->pluck('grade_level_id')
                ->toArray();

            $query->where(function ($q) use ($teacherGrades, $teacherClassRooms) {
                $q->whereIn('grade_level_id', $teacherGrades)
                    ->doesntHave('classRooms')
                    ->orWhereHas('classRooms', function ($subQ) use ($teacherClassRooms) {
                        $subQ->whereIn('class_room_id', $teacherClassRooms);
                    });
            });
        } else {
            $query->where('id', '<', 0);
        }

        return $query->orderBy('activity_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);
    }
}
