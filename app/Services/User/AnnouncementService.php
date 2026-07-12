<?php

namespace App\Services\User;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Jobs\SendAnnouncementNotification;
use App\Models\Enrollment;
use App\Models\User;

class AnnouncementService
{
    public function create(array $data): Announcement
    {
        $announcement = Announcement::create($data);

        SendAnnouncementNotification::dispatch(
            $announcement->id,
            $announcement->audience,
            $announcement->title,
            $announcement->description ?? ''
        );

        return $announcement;
    }

    public function delete(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
    }

private function getBaseQueryForUser(User $user, ?int $specificStudentId = null)
    {
        if ($user->student) {
           $enrollment = $user->student->enrollments()
                ->whereHas('academicYear', function ($q) {
                    $q->whereDate('start_date', '<=', now())
                      ->whereDate('end_date', '>=', now());
                })->latest()->first();

            if (!$enrollment) {
                return Announcement::query()->where(function ($q) {
                    $q->where('audience', Announcement::AUDIENCE_BOTH)
                      ->orWhere(function ($sq) {
                          $sq->where('audience', Announcement::AUDIENCE_STUDENT)
                             ->whereNull('grade_level_id')
                             ->whereNull('class_room_id');
                      });
                });
            }

            return Announcement::query()->where(function ($query) use ($enrollment) {
                $query->where('audience', Announcement::AUDIENCE_BOTH)
                      ->orWhere(function ($q) use ($enrollment) {
                          $q->where('audience', Announcement::AUDIENCE_STUDENT)
                            ->where(function ($subQ) use ($enrollment) {
                                $subQ->whereNull('grade_level_id')
                                     ->orWhere(function ($gq) use ($enrollment) {
                                         $gq->where('grade_level_id', $enrollment->grade_level_id)
                                            ->whereNull('class_room_id');
                                     })
                                     ->orWhere('class_room_id', $enrollment->class_room_id);
                            });
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

            if ($enrollments->isEmpty()) {
                return Announcement::query()->where(function ($q) {
                    $q->where('audience', Announcement::AUDIENCE_BOTH)
                      ->orWhere(function ($sq) {
                          $sq->where('audience', Announcement::AUDIENCE_STUDENT)
                             ->whereNull('grade_level_id')
                             ->whereNull('class_room_id');
                      });
                });
            }

            return Announcement::query()->where(function ($query) use ($enrollments) {
                $query->where('audience', Announcement::AUDIENCE_BOTH)
                      ->orWhere(function ($q) use ($enrollments) {
                          $q->where('audience', Announcement::AUDIENCE_STUDENT)
                            ->where(function ($subQ) use ($enrollments) {
                                $subQ->whereNull('grade_level_id');
                                foreach ($enrollments as $enrollment) {
                                    $subQ->orWhere(function ($gq) use ($enrollment) {
                                        $gq->where('grade_level_id', $enrollment->grade_level_id)
                                           ->whereNull('class_room_id');
                                    })
                                    ->orWhere('class_room_id', $enrollment->class_room_id);
                                }
                            });
                      });
            });
        }

        if ($user->staff) {
            return Announcement::query()->whereIn('audience', [Announcement::AUDIENCE_STAFF, Announcement::AUDIENCE_BOTH]);
        }

        return Announcement::query()->where('id', '<', 0);
    }

    public function forStaff(User $user): LengthAwarePaginator
    {
        return $this->getBaseQueryForUser($user)
            ->latest()
            ->paginate(20);
    }

    public function forStudent(User $user): LengthAwarePaginator
    {
        return $this->getBaseQueryForUser($user)
            ->with(['gradeLevel:id,name', 'classRoom:id,name'])
            ->latest()
            ->paginate(20);
    }

    public function forGuardian(User $user, ?int $studentId = null): LengthAwarePaginator
    {
        return $this->getBaseQueryForUser($user,$studentId)
            ->with(['gradeLevel:id,name', 'classRoom:id,name'])
            ->latest()
            ->paginate(20);
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
        $announcementIds = $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        $syncData = [];
        $now = now();
        foreach ($announcementIds as $id) {
            $syncData[$id] = ['read_at' => $now];
        }

        if (!empty($syncData)) {
            $user->readAnnouncements()->syncWithoutDetaching($syncData);
        }
    }
}
