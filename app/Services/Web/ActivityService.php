<?php

namespace App\Services\Web;

use App\ApiResource;
use App\Jobs\SendPushNotification;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityService
{

    use ApiResource;

    public function addActivity(array $data)
    {
        $activity = Activity::create($data);

        $enrollmentsQuery = Enrollment::where('grade_level_id', $activity->grade_level_id)
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            });

        if ($activity->class_room_id) {
            $enrollmentsQuery->where('class_room_id', $activity->class_room_id);
        }

        $studentIds = $enrollmentsQuery->pluck('student_id');

        $usersIds = User::whereHas('student', function ($q) use ($studentIds) {
            $q->whereIn('id', $studentIds);
        })
            ->orWhereHas('guardian', function ($q) use ($studentIds) {
                $q->whereHas('students', function ($sq) use ($studentIds) {
                    $sq->whereIn('id', $studentIds);
                });
            })
            ->pluck('id')->toArray();

        if (!empty($usersIds)) {
            SendPushNotification::dispatch(
                $usersIds,
                'نشاط مدرسي جديد',
                'تمت إضافة نشاط جديد: ' . $activity->activity_name,
                [
                    'activity_id' => (string) $activity->id,
                    'type'        => 'activity'
                ]
            );
        }

        return $activity->load(['gradeLevel:id,name', 'classRoom:id,name']);
    }

    public function showActivities(Student $student): LengthAwarePaginator // لاحظ تغيير نوع الإرجاع
    {
        $enrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->latest()
            ->first();

        if (!$enrollment) {
            $emptyPaginator = new LengthAwarePaginator([], 0, 20);
            $emptyPaginator->withPath(request()->url()); // إخبار Laravel بالرابط الحالي
            return $emptyPaginator;
        }

        return Activity::query()
            ->where('grade_level_id', $enrollment->grade_level_id)
            ->where(function ($query) use ($enrollment) {
                $query->whereNull('class_room_id')
                    ->orWhere('class_room_id', $enrollment->class_room_id);
            })
            ->with(['gradeLevel:id,name', 'classRoom:id,name'])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->paginate(20);
    }

    public function updateActivity(Activity $activity, array $data)
    {
        $activity->update($data);

        return $activity->load(['gradeLevel:id,name', 'classRoom:id,name']);
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

            if (!$enrollment) return Activity::query()->where('id', '<', 0);

            return Activity::query()
                ->where('grade_level_id', $enrollment->grade_level_id)
                ->where(function ($q) use ($enrollment) {
                    $q->whereNull('class_room_id')
                        ->orWhere('class_room_id', $enrollment->class_room_id);
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

            if ($enrollments->isEmpty()) return Activity::query()->where('id', '<', 0);

            return Activity::query()->where(function ($query) use ($enrollments) {
                foreach ($enrollments as $enrollment) {
                    $query->orWhere(function ($q) use ($enrollment) {
                        $q->where('grade_level_id', $enrollment->grade_level_id)
                            ->where(function ($subQ) use ($enrollment) {
                                $subQ->whereNull('class_room_id')
                                    ->orWhere('class_room_id', $enrollment->class_room_id);
                            });
                    });
                }
            });
        }

        return Activity::query()->where('id', '<', 0);
    }

    public function unreadCount(User $user, ?int $studentId = null): int
    {
        return $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }

    public function markAllAsRead(User $user, ?int $studentId = null): void
    {
        $activityIds = $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        $syncData = [];
        $now = now();
        foreach ($activityIds as $id) {
            $syncData[$id] = ['read_at' => $now];
        }

        if (!empty($syncData)) {
            $user->readActivities()->syncWithoutDetaching($syncData);
        }
    }
}
