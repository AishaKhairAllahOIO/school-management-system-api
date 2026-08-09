<?php

namespace App\Services\User;

use App\Jobs\SendPushNotification;
use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\SchoolLaw;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AlertService
{
    // public function createStudentAbsence(Enrollment $enrollment, array $meta = []): Alert
    // {
    //     $absenceDate = $meta['date'] ?? now()->toDateString();

    //     $existingAbsence = Alert::where('notifiable_type', Enrollment::class)
    //         ->where('notifiable_id', $enrollment->id)
    //         ->where('type', Alert::TYPE_ABSENCE)
    //         ->where('meta->date', $absenceDate)
    //         ->exists();

    //     if ($existingAbsence) {
    //         throw new Exception("An absence alert has already been created for this student on {$absenceDate}. You cannot create two absence alerts on the same day.");
    //     }

    //     $alert = $this->createStudentAlert(
    //         $enrollment,
    //         Alert::TYPE_ABSENCE,
    //         'تنبيه غياب',
    //         'تم تسجيل غياب الطالب اليوم.',
    //         array_merge(['date' => $absenceDate], $meta)
    //     );

    //     $absenceCount = Alert::where('notifiable_type', Enrollment::class)
    //         ->where('notifiable_id', $enrollment->id)
    //         ->where('type', Alert::TYPE_ABSENCE)
    //         ->count();

    //     if ($absenceCount == 5) {
    //         $this->createStudentWarning(
    //             $enrollment,
    //             $meta,
    //             'تحذير: اقتراب تجاوز الحد المسموح للغياب',
    //             'يرجى الانتباه: لقد بلغت عدد مرات غياب الطالب 5 مرات. عند الوصول إلى 7 غيابات سيتم إصدار قرار فصل بحق الطالب وإيقاف حسابه في نهاية الفصل الدراسي ما لم يتم التبرير.'
    //         );
    //     }

    //     if ($absenceCount == 7) {
    //         $this->createStudentExpulsion($enrollment, ['law_id' => 1]);
    //     }

    //     return $alert;
    // }
    public function createStudentAbsence(Enrollment $enrollment, array $meta = []): Alert
    {
        $absenceDate = $meta['date'] ?? now()->toDateString();

        $existingAbsence = Alert::where('notifiable_type', Enrollment::class)
            ->where('notifiable_id', $enrollment->id)
            ->where('type', Alert::TYPE_ABSENCE)
            ->where('meta->date', $absenceDate)
            ->exists();

        if ($existingAbsence) {
            throw new Exception("An absence alert has already been created for this student on {$absenceDate}. You cannot create two absence alerts on the same day.");
        }

        // 1. إنشاء تنبيه الغياب الأساسي
        $alert = $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_ABSENCE,
            'تنبيه غياب',
            'تم تسجيل غياب الطالب اليوم.',
            array_merge(['date' => $absenceDate], $meta)
        );

        $semesterId = $enrollment->semester_id; // تأكدي من حقل الفصل في جدول الـ enrollments لديكِ
        $setting = \App\Models\StudentAttendanceSetting::where('semester_id', $semesterId)->first();

        $allowedDays = $setting ? $setting->allowed_absence_days : 10;

        $warningLimit = max(0, $allowedDays - 2);

        $absenceCount = Alert::where('notifiable_type', Enrollment::class)
            ->where('notifiable_id', $enrollment->id)
            ->where('type', Alert::TYPE_ABSENCE)
            ->count();

        if ($absenceCount == $warningLimit) {
            $this->createStudentWarning(
                $enrollment,
                $meta,
                'تحذير: اقتراب تجاوز الحد المسموح للغياب',
                "يرجى الانتباه: لقد بلغ إجمالي غياب الطالب {$absenceCount} مرات (الحد المسموح هو {$allowedDays} أيام). عند تجاوز الحد سيتم إصدار قرار فصل وإيقاف الحساب نهاية الفصل ما لم يتم التبرير."
            );
        }

        if ($absenceCount > $allowedDays) {
            $this->createStudentExpulsion($enrollment, ['law_id' => 1]);
        }

        return $alert;
    }


public function createSystemNotice(Staff $staff, string $title, string $description, array $meta = []): Alert
    {
        return $this->createStaffAlert(
            $staff,
            Alert::TYPE_SYSTEM_NOTICE,
            $title,
            $description,
            $meta
        );
    }
    public function createStudentExpulsion(Enrollment $enrollment, array $meta = []): Alert
    {
        $law = SchoolLaw::find($meta['law_id'] ?? null);

        $lawTitle = $law ? $law->title : 'تجاوز الحد الأقصى للغياب';
        $lawDescription = $law ? $law->description : 'تجاوز الطالب الحد الأقصى المسموح به للغياب خلال الفصل الدراسي.';

        if (isset($meta['law_id'])) {
            unset($meta['law_id']);
        }

        $meta['law_title'] = $lawTitle;

        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_EXPULSION,
            'قرار فصل',
            "تم إصدار قرار فصل بحق الطالب. السبب: {$lawDescription}",
            $meta
        );
    }

    public function createStudentWarning(Enrollment $enrollment, array $meta = [], ?string $title = null, ?string $description = null): Alert
    {
        $absenceCount = Alert::where('notifiable_type', Enrollment::class)
            ->where('notifiable_id', $enrollment->id)
            ->where('type', Alert::TYPE_ABSENCE)
            ->count();

        $meta['absence_count'] = $absenceCount;

        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_WARNING,
            $title ?? 'تنبيه تحذيري',
            $description ?? 'يرجى الانتباه لوضع الطالب لتجنب الإجراءات الإدارية.',
            $meta
        );
    }

    public function createStudentLate(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_LATE,
            'تنبيه تأخر',
            'تم تسجيل تأخر الطالب عن الدوام.',
            $meta
        );
    }

    public function createStudentBehavior(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_BEHAVIOR,
            'تنبيه سلوك',
            'تم تسجيل ملاحظة سلوكية للطالب.',
            $meta
        );
    }

    public function createStudentPayment(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_PAYMENT,
            'تنبيه دفع',
            'يوجد تأخر في دفع قسط.',
            $meta
        );
    }

    public function createStudentPayed(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_PAYED,
            'تنبيه دفع',
            'تم تسديد دفعة من القسط.',
            $meta
        );
    }

    public function createStudentHomework(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_HOMEWORK,
            'تنبيه واجب',
            'لم يكتب الطالب الواجب المنزلي.',
            array_merge(['date' => now()->toDateString()], $meta)
        );
    }

    public function createStudentEscape(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_ESCAPE,
            'تنبيه هروب',
            'تم تسجيل حالة هروب الطالب.',
            $meta
        );
    }

    public function createStaffAbsence(Staff $staff, array $meta = []): Alert
    {
        return $this->createStaffAlert(
            $staff,
            Alert::TYPE_ABSENCE,
            'تنبيه غياب',
            'تم تسجيل غيابك اليوم.',
            array_merge(['date' => now()->toDateString()], $meta)
        );
    }

    public function createStaffLate(Staff $staff, array $meta = []): Alert
    {
        return $this->createStaffAlert(
            $staff,
            Alert::TYPE_LATE,
            'تنبيه تأخر',
            'تم تسجيل تأخرك عن الدوام.',
            $meta
        );
    }

    public function createStaffSalary(Staff $staff, array $meta = []): Alert
    {
        return $this->createStaffAlert(
            $staff,
            Alert::TYPE_SALARY,
            'تنبيه راتب',
            'تم رفع الراتب الشهري يرجى مراجعة حساب شام كاش.',
            array_merge(['month' => now()->format('F Y')], $meta)
        );
    }

    private function createStudentAlert(
        Enrollment $enrollment,
        string $type,
        string $title,
        string $desc,
        array $meta
    ): Alert {
        $alert = Alert::create([
            'notifiable_id' => $enrollment->id,
            'notifiable_type' => Enrollment::class,
            'type' => $type,
            'audience' => Alert::AUDIENCE_STUDENT,
            'title' => $title,
            'description' => $desc,
            'meta' => $meta,
            'created_by' => Auth::id(),
        ]);

        $student = $enrollment->student;
        $users = collect([$student->user, $student->guardian?->user])->filter();

        $this->dispatch($alert, $users);

        return $alert;
    }

    private function createStaffAlert(
        Staff $staff,
        string $type,
        string $title,
        string $desc,
        array $meta
    ): Alert {
        $alert = Alert::create([
            'notifiable_id' => $staff->id,
            'notifiable_type' => Staff::class,
            'type' => $type,
            'audience' => Alert::AUDIENCE_STAFF,
            'title' => $title,
            'description' => $desc,
            'meta' => $meta,
            'created_by' => Auth::id(),
        ]);

        $users = collect([$staff->user])->filter();

        $this->dispatch($alert, $users);

        return $alert;
    }

    private function dispatch(Alert $alert, Collection $users): void
    {
        if ($users->isEmpty()) {
            return;
        }

        SendPushNotification::dispatch(
            $users->pluck('id')->toArray(),
            $alert->title,
            $alert->description,
            [
                'alert_id' => (string) $alert->id,
                'alert_type' => $alert->type,
                'audience' => $alert->audience,
            ]
        );
    }

    public function showStaffAlerts(Staff $staff): LengthAwarePaginator
    {
        return Alert::where('notifiable_type', Staff::class)
            ->where('notifiable_id', $staff->id)
            ->whereNotIn('type', [Alert::TYPE_SALARY, Alert::TYPE_SYSTEM_NOTICE])
            ->latest()
            ->paginate(20);
    }

    public function showStaffPaymentAlerts(Staff $staff): LengthAwarePaginator
    {
        return Alert::where('notifiable_type', Staff::class)
            ->where('notifiable_id', $staff->id)
            ->whereIn('type', [Alert::TYPE_SALARY])
            ->latest()
            ->paginate(20);
    }

    public function showStudentAlerts(Student $student): LengthAwarePaginator
    {
        $enrollmentIds = $student->enrollments()->pluck('id');

        return Alert::where('notifiable_type', Enrollment::class)
            ->whereIn('notifiable_id', $enrollmentIds)
            ->whereNotIn('type', [Alert::TYPE_PAYMENT, Alert::TYPE_PAYED])
            ->latest()
            ->paginate(20);
    }

    public function showStudentPaymentAlerts(Student $student): LengthAwarePaginator
    {
        $enrollmentIds = $student->enrollments()->pluck('id');

        return Alert::where('notifiable_type', Enrollment::class)
            ->whereIn('notifiable_id', $enrollmentIds)
            ->whereIn('type', [Alert::TYPE_PAYMENT, Alert::TYPE_PAYED])
            ->latest()
            ->paginate(20);
    }

    public function createBatchStudentAlerts(array $enrollmentIds, string $type, array $meta = [], ?string $title = null, ?string $description = null): Collection
    {
        $enrollments = Enrollment::with(['student.user', 'student.guardian.user'])->whereIn('id', $enrollmentIds)->get();
        $alerts = collect();

        foreach ($enrollments as $enrollment) {
            $alert = match ($type) {
                Alert::TYPE_ABSENCE => $this->createStudentAbsence($enrollment, $meta),
                Alert::TYPE_LATE => $this->createStudentLate($enrollment, $meta),
                Alert::TYPE_BEHAVIOR => $this->createStudentBehavior($enrollment, $meta),
                Alert::TYPE_PAYMENT => $this->createStudentPayment($enrollment, $meta),
                Alert::TYPE_PAYED => $this->createStudentPayed($enrollment, $meta),
                Alert::TYPE_ESCAPE => $this->createStudentEscape($enrollment, $meta),
                Alert::TYPE_HOMEWORK => $this->createStudentHomework($enrollment, $meta),
                Alert::TYPE_WARNING => $this->createStudentWarning($enrollment, $meta, $title, $description),
                Alert::TYPE_EXPULSION => $this->createStudentExpulsion($enrollment, $meta),
                default => $this->createStudentAlert($enrollment, $type, $title ?? '', $description ?? '', $meta),
            };
            $alerts->push($alert);
        }

        return $alerts;
    }

    public function createBatchStaffAlerts(array $staffIds, string $type, array $meta = [], ?string $title = null, ?string $description = null): Collection
    {
        $staffMembers = Staff::with('user')->whereIn('id', $staffIds)->get();
        $alerts = collect();

        foreach ($staffMembers as $staff) {
            $alert = match ($type) {
                Alert::TYPE_ABSENCE => $this->createStaffAbsence($staff, $meta),
                Alert::TYPE_LATE => $this->createStaffLate($staff, $meta),
                Alert::TYPE_SALARY => $this->createStaffSalary($staff, $meta),
                default => $this->createStaffAlert($staff, $type, $title ?? '', $description ?? '', $meta),
            };
            $alerts->push($alert);
        }

        return $alerts;
    }

    public function createManual(array $data): Collection
    {
        if ($data['audience'] === Alert::AUDIENCE_STUDENT) {
            return $this->createBatchStudentAlerts(
                $data['enrollment_ids'],
                $data['type'],
                $data['meta'] ?? [],
                $data['title'] ?? null,
                $data['description'] ?? null
            );
        }

        return $this->createBatchStaffAlerts(
            $data['staff_ids'],
            $data['type'],
            $data['meta'] ?? [],
            $data['title'] ?? null,
            $data['description'] ?? null
        );
    }

    public function advisorAlerts(array $data): Collection
    {
        return $this->createBatchStudentAlerts(
            $data['enrollment_ids'],
            $data['type'],
            $data['meta'] ?? [],
            $data['title'] ?? null,
            $data['description'] ?? null
        );
    }

    public function createPaymentAlerts(array $data): Collection
    {
        return $this->createBatchStudentAlerts(
            $data['enrollment_ids'],
            $data['type'],
            $data['meta'] ?? [],
            $data['title'] ?? null,
            $data['description'] ?? null
        );
    }

    public function createStaffAlerts(array $data): Collection
    {
        return $this->createBatchStaffAlerts(
            $data['staff_ids'],
            $data['type'],
            $data['meta'] ?? [],
            $data['title'] ?? null,
            $data['description'] ?? null
        );
    }

    public function deleteAlert(int $id): void
    {
        $alert = Alert::findOrFail($id);
        $alert->delete();
    }

    public function getPendingExpulsions(): LengthAwarePaginator
    {
        return Enrollment::whereHas('alerts', function ($query) {
            $query->where('type', Alert::TYPE_EXPULSION);
        })
            ->with([
                'student.user',
                'gradeLevel',
                'classRoom',
                'alerts' => function ($q) {
                    $q->where('type', Alert::TYPE_EXPULSION);
                }
            ])->paginate(15);
    }

    public function executeConfirmedExpulsions(array $enrollmentIds): array
    {
        $enrollmentsToExpel = Enrollment::whereIn('id', $enrollmentIds)
            ->with(['student.user', 'student.guardian.user', 'student.guardian.students'])
            ->get();

        $expelledCount = 0;

        foreach ($enrollmentsToExpel as $enrollment) {
            $studentUser = $enrollment->student->user;
            $guardian = $enrollment->student->guardian;

            if ($studentUser) {
                $studentUser->update(['account_status' => 'disabled']);
                $expelledCount++;
            }

            if ($guardian && $guardian->user) {
                $activeStudents = $guardian->students()->whereHas('user', function ($q) {
                    $q->where('account_status', 'enabled');
                })->count();

                if ($activeStudents === 0) {
                    $guardian->user->update(['account_status' => 'disabled']);
                }
            }
        }

        return ['count' => $expelledCount];
    }

    private function getBaseAlertQueryForUser(User $user, ?int $studentId = null)
    {
        if ($user->hasRole('student') && $user->student) {
            $enrollmentIds = $user->student->enrollments()->pluck('id');
            return Alert::where('notifiable_type', Enrollment::class)->whereIn('notifiable_id', $enrollmentIds);
        }

        if ($user->hasRole('guardian') && $user->guardian) {
            $studentsQuery = $user->guardian->students();

            if ($studentId) {
                $isMyChild = $user->guardian->students()->where('students.id', $studentId)->exists();

                if (!$isMyChild) {
                    throw new AccessDeniedHttpException('You are not allowed to access this. This child is not yours.', null, 403);
                }

                $studentsQuery->where('students.id', $studentId);
            }

            $studentIds = $studentsQuery->pluck('students.id');
            $enrollmentIds = Enrollment::whereIn('student_id', $studentIds)->pluck('id');

            return Alert::where('notifiable_type', Enrollment::class)->whereIn('notifiable_id', $enrollmentIds);
        }

        if ($user->hasAnyRole(['super_admin', 'adviser', 'teacher']) || $user->staff) {
            $staffId = $user->staff?->id;

            if ($staffId) {
                return Alert::where('notifiable_type', Staff::class)->where('notifiable_id', $staffId);
            }
        }

        return Alert::where('id', '<', 0);
    }

    public function unreadCountForUser(User $user, ?int $studentId = null): array
    {
        $baseQuery = $this->getBaseAlertQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        $financialTypes = [Alert::TYPE_PAYMENT, Alert::TYPE_PAYED, Alert::TYPE_SALARY];
        $systemTypes = [Alert::TYPE_SYSTEM_NOTICE];

        return [
            'alerts' => (clone $baseQuery)->whereNotIn('type', array_merge($financialTypes, $systemTypes))->count(),

            'payment_alerts' => (clone $baseQuery)->whereIn('type', $financialTypes)->count(),

            'system_alerts' => (clone $baseQuery)->whereIn('type', $systemTypes)->count(),
        ];
    }

    public function markAllReadForUser(User $user, string $category = 'all', ?int $studentId = null): array
    {
        $baseQuery = $this->getBaseAlertQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        $financialTypes = [Alert::TYPE_PAYMENT, Alert::TYPE_PAYED, Alert::TYPE_SALARY];
        $systemTypes = [Alert::TYPE_SYSTEM_NOTICE];

        if ($category === 'financial') {
            $baseQuery->whereIn('type', $financialTypes);
        } elseif ($category === 'system') {
            $baseQuery->whereIn('type', $systemTypes);
        } elseif ($category === 'general') {
            $baseQuery->whereNotIn('type', array_merge($financialTypes, $systemTypes));
        }

        $unreadAlertIds = $baseQuery->pluck('id');

        $syncData = [];
        $now = now();
        foreach ($unreadAlertIds as $id) {
            $syncData[$id] = ['read_at' => $now];
        }

        if (!empty($syncData)) {
            $user->readAlerts()->syncWithoutDetaching($syncData);
        }

        return $this->unreadCountForUser($user, $studentId);
    }




    public function showSystemNotices(Staff $staff): LengthAwarePaginator
    {
        return Alert::where('notifiable_type', Staff::class)
            ->where('notifiable_id', $staff->id)
            ->where('type', Alert::TYPE_SYSTEM_NOTICE)
            ->latest()
            ->paginate(20);
    }


    public function unreadSystemNoticesCount(User $user): int
    {
       $query = $this->getBaseAlertQueryForUser($user);

       return $query
       ->where('type',Alert::TYPE_SYSTEM_NOTICE)
       ->whereDoesntHave('readers',function($query)use ($user){
        $query->where('user_id',$user->id);
       })
       ->count();
    }


    public function markAllSystemNoticesAsRead(User $user): void
    {
        $unreadAlertIds = clone $this->getBaseAlertQueryForUser($user)
            ->where('type', Alert::TYPE_SYSTEM_NOTICE)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        if ($unreadAlertIds->isNotEmpty()) {
            $syncData = [];
            $now = now();
            foreach ($unreadAlertIds as $id) {
                $syncData[$id] = ['read_at' => $now];
            }

            $user->readAlerts()->syncWithoutDetaching($syncData);
        }
    }


    public function getAlertsCreatedByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = Alert::with(['notifiable' => function ($morphTo) {
            $morphTo->morphWith([
                Enrollment::class => ['student.user:id,first_name,last_name'],
                Staff::class => ['user:id,first_name,last_name'],
            ]);
        }]);

        if (!$user->hasRole('super_admin')) {
            $query->where('created_by', $user->id);
        }

        return $query->latest()->paginate($perPage);
    }


    public function updateAlert(int $id, array $data, User $user): Alert
    {
        $alert = Alert::findOrFail($id);

        if ($alert->created_by !== $user->id && !$user->hasRole('super_admin')) {
            throw new AccessDeniedHttpException('Update denied you are not the creater',null,403);
        }

        if (isset($data['type'])) {
            $studentTypes = [
                Alert::TYPE_ABSENCE, Alert::TYPE_LATE, Alert::TYPE_BEHAVIOR,
                Alert::TYPE_PAYMENT, Alert::TYPE_PAYED, Alert::TYPE_ESCAPE,
                Alert::TYPE_HOMEWORK, Alert::TYPE_WARNING, Alert::TYPE_EXPULSION
            ];

            $staffTypes = [
                Alert::TYPE_ABSENCE, Alert::TYPE_LATE, Alert::TYPE_SALARY, Alert::TYPE_SYSTEM_NOTICE
            ];

            if ($alert->audience === Alert::AUDIENCE_STUDENT && !in_array($data['type'], $studentTypes)) {
                throw new InvalidArgumentException('The type of the alert does not match the student audience.',422);
            }

            if ($alert->audience === Alert::AUDIENCE_STAFF && !in_array($data['type'], $staffTypes)) {
                throw new InvalidArgumentException('The type of the alert dose not match the staff audience.', 422);
            }
        }

        $alert->update([
            'title'       => $data['title'] ?? $alert->title,
            'description' => $data['description'] ?? $alert->description,
            'type'        => $data['type'] ?? $alert->type,
            'meta'        => isset($data['meta']) ? array_merge($alert->meta ?? [], $data['meta']) : $alert->meta,
        ]);

        return $alert;
    }

    public function deleteBatchAlerts(array $alertIds, User $user): int
    {
        $alerts = Alert::whereIn('id', $alertIds)->get();
        $deletedCount = 0;

        foreach ($alerts as $alert) {
            if ($alert->created_by === $user->id || $user->hasRole('super_admin')) {
                $alert->delete();
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
