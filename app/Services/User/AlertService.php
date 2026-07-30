<?php

namespace App\Services\User;

use App\Jobs\SendPushNotification;
use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\SchoolLaw;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AlertService
{
    public function createStudentAbsence(Enrollment $enrollment, array $meta = []): Alert
    {
        $alert = $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_ABSENCE,
            'تنبيه غياب',
            'تم تسجيل غياب الطالب اليوم.',
            array_merge(['date' => now()->toDateString()], $meta)
        );

        $absenceCount = Alert::where('notifiable_type', Enrollment::class)
            ->where('notifiable_id', $enrollment->id)
            ->where('type', Alert::TYPE_ABSENCE)
            ->count();

            if ($absenceCount == 5) {
            $this->createStudentWarning(
                $enrollment,
                $meta,
                'تحذير: اقتراب تجاوز الحد المسموح للغياب',
                'يرجى الانتباه: لقد بلغت عدد مرات غياب الطالب 5 مرات. عند الوصول إلى 7 غيابات سيتم إصدار قرار فصل بحق الطالب وإيقاف حسابه في نهاية الفصل الدراسي ما لم يتم التبرير.'
            );
        }

            if ($absenceCount == 7) {
            $this->createStudentExpulsion($enrollment, ['law_id' => 3]);

        }

        return $alert;

    }

    public function createStudentExpulsion(Enrollment $enrollment, array $meta = []): Alert
    {
        $law = SchoolLaw::find($meta['law_id'] ?? null);
        $lawTitle = $law ? $law->title : 'تجاوز الحد الأقصى للغياب';

        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_EXPULSION,
            'قرار فصل',
            "تم إصدار قرار فصل بحق الطالب لمخالفته القانون المدرسي: {$lawTitle}",
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
            ->whereNotIn('type', [Alert::TYPE_SALARY])
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
                    throw new AccessDeniedHttpException('هذا الطالب لا يتبع لرعايتك، غير مصرح لك بالوصول لبياناته.');
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

        return [
            'alerts' => (clone $baseQuery)->whereNotIn('type', $financialTypes)->count(),
            'payment_alerts' => (clone $baseQuery)->whereIn('type', $financialTypes)->count(),
        ];
    }
    public function markAllReadForUser(User $user, string $category = 'all', ?int $studentId = null): array
    {
        $baseQuery = $this->getBaseAlertQueryForUser($user, $studentId)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        $financialTypes = [Alert::TYPE_PAYMENT, Alert::TYPE_PAYED, Alert::TYPE_SALARY];

        if ($category === 'financial') {
            $baseQuery->whereIn('type', $financialTypes);
        } elseif ($category === 'general') {
            $baseQuery->whereNotIn('type', $financialTypes);
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

}
