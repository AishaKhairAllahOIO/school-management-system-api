<?php

namespace App\Services\User;

use App\Jobs\SendPushNotification;
use App\Models\Announcement;
use App\Models\GradeConfiguration;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Arr;

class AnnouncementService
{
    public function create(array $data): Announcement
    {
        $classRoomIds = $data['class_room_ids'] ?? [];
        $announcementData = Arr::except($data, ['class_room_ids']);

        $announcement = Announcement::create($announcementData);

        if (!empty($classRoomIds)) {
            $announcement->classRooms()->attach($classRoomIds);
        }


        $this->sendAnnouncementNotification(
            $announcement,
            'new announcement has been published.'
        );

        return $announcement;
    }

    public function delete(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
    }

    public function update(int $id, array $data): Announcement
    {
        $announcement = Announcement::findOrFail($id);

        $classRoomIds = $data['class_room_ids'] ?? null;
        $announcementData = Arr::except($data, ['class_room_ids']);

        $announcement->update($announcementData);

        if ($classRoomIds !== null) {
            $announcement->classRooms()->sync($classRoomIds);
        }

        $announcement->readers()->detach();

        $this->sendAnnouncementNotification(
            $announcement,
            'announcement has been updated.'
        );

        return $announcement;
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
                                ->doesntHave('classRooms'); // التعديل هنا
                        });
                });
            }

            return Announcement::query()->where(function ($query) use ($enrollment) {
                $query->where('audience', Announcement::AUDIENCE_BOTH)
                    ->orWhere(function ($q) use ($enrollment) {
                        $q->where('audience', Announcement::AUDIENCE_STUDENT)
                            ->where(function ($subQ) use ($enrollment) {
                                // إعلان عام لجميع الطلاب
                                $subQ->whereNull('grade_level_id')
                                    // أو إعلان مخصص لصف الطالب وشعبته
                                    ->orWhere(function ($gq) use ($enrollment) {
                                    $gq->where('grade_level_id', $enrollment->grade_level_id)
                                        ->where(function ($cq) use ($enrollment) {
                                            $cq->doesntHave('classRooms') // لكل شعب الصف
                                                ->orWhereHas('classRooms', function ($rq) use ($enrollment) {
                                                    $rq->where('class_rooms.id', $enrollment->class_room_id); // لشعبة الطالب تحديداً
                                                });
                                        });
                                });
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
                                ->doesntHave('classRooms');
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
                                            ->where(function ($cq) use ($enrollment) {
                                                $cq->doesntHave('classRooms')
                                                    ->orWhereHas('classRooms', function ($rq) use ($enrollment) {
                                                        $rq->where('class_rooms.id', $enrollment->class_room_id);
                                                    });
                                            });
                                    });
                                }
                            });
                    });
            });
        }

        if ($user->hasAnyRole(['super_admin', 'adviser', 'teacher']) || $user->staff) {
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
            ->with(['gradeLevel:id,name', 'classRooms:id,name']) // تم تعديل classRoom إلى classRooms
            ->latest()
            ->paginate(20);
    }

    public function forGuardian(User $user, ?int $studentId = null): LengthAwarePaginator
    {
        return $this->getBaseQueryForUser($user, $studentId)
            ->with(['gradeLevel:id,name', 'classRooms:id,name'])
            ->latest()
            ->paginate(20);
    }

    private function resolveReaderUser(User $user, ?int $studentId): ?User
    {
        if ($user->student) {
            return $user;
        }

        if ($user->guardian) {
            return $user;
        }

        return $user;
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

    // //////////////////////////////

    public function markAllAsRead(User $user, ?int $studentId = null): array
    {
        $readerUser = $this->resolveReaderUser($user, $studentId);

        if (!$readerUser) {
            return [
                'unread_count' => 0
            ];
        }

        $announcementIds = $this->getBaseQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($readerUser) {
                $q->where('user_id', $readerUser->id);
            })
            ->pluck('id');


        if ($announcementIds->isNotEmpty()) {

            $syncData = [];

            foreach ($announcementIds as $id) {
                $syncData[$id] = [
                    'read_at' => now()
                ];
            }

            $readerUser->readAnnouncements()
                ->syncWithoutDetaching($syncData);
        }


        return [
            'unread_count' => $this->unreadCount($user, $studentId)
        ];
    }


    public function getAdminAnnouncements(User $user): LengthAwarePaginator
    {
        $query = Announcement::query()->with(['gradeLevel:id,name', 'classRooms:id,name']);

        if ($user->hasRole('super_admin')) {
        } elseif ($user->hasRole('adviser')) {
            $advisorGradeIds = GradeConfiguration::where('supervisor_id', $user->id)
                ->whereHas('academicYear', function ($q) {
                    $q->where('is_current', true);
                })
                ->pluck('grade_level_id')
                ->toArray();

            $query->whereIn('audience', [Announcement::AUDIENCE_STUDENT])
                ->where(function ($q) use ($advisorGradeIds) {
                    $q->whereIn('grade_level_id', $advisorGradeIds)
                        ->orWhereNull('grade_level_id');
                });
        } else {
            $query->where('id', '<', 0);
        }

        return $query->latest()->paginate(20);
    }

    private function sendAnnouncementNotification(Announcement $announcement, string $message)
    {
        $targetUserIds = [];

        if (in_array($announcement->audience, [Announcement::AUDIENCE_STAFF, Announcement::AUDIENCE_BOTH])) {
            $staffUserIds = User::whereHas('staff')->pluck('id')->toArray();
            $targetUserIds = array_merge($targetUserIds, $staffUserIds);
        }

        if (in_array($announcement->audience, [Announcement::AUDIENCE_STUDENT, Announcement::AUDIENCE_BOTH])) {
            $enrollmentsQuery = Enrollment::whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            });

            if ($announcement->grade_level_id) {
                $enrollmentsQuery->where('grade_level_id', $announcement->grade_level_id);
            }

            $studentIds = $enrollmentsQuery->pluck('student_id');

            $studentAndGuardianIds = User::whereHas('student', function ($q) use ($studentIds) {
                $q->whereIn('id', $studentIds);
            })->orWhereHas('guardian.students', function ($q) use ($studentIds) {
                $q->whereIn('students.id', $studentIds);
            })->pluck('id')->toArray();

            $targetUserIds = array_merge($targetUserIds, $studentAndGuardianIds);
        }

        $targetUserIds = array_unique($targetUserIds);

        if (!empty($targetUserIds)) {
            SendPushNotification::dispatch(
                $targetUserIds,
                $message,
                $announcement->title,
                [
                    'announcement_id' => (string) $announcement->id,
                    'type' => 'announcement'
                ]
            );
        }
    }
}
