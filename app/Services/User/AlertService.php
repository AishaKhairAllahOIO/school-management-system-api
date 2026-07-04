<?php

namespace App\Services\User;

use App\Jobs\SendPushNotification;
use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AlertService
{
    // ========== تنبيهات الطلاب (للطالب + ولي أمره) ==========

    public function createStudentAbsence(Enrollment $enrollment, array $meta = []): Alert
    {
        return $this->createStudentAlert(
            $enrollment,
            Alert::TYPE_ABSENCE,
            'تنبيه غياب',
            'تم تسجيل غياب الطالب اليوم.',
            array_merge(['date' => now()->toDateString()], $meta)
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
    public function createStudentHomework(Enrollment $enrollment,Staff $teacher, array $meta = []): Alert
    {
     $subject = $teacher->teacherAssignments
        ->firstWhere('class_room_id', $enrollment->class_room_id)
        ?->subject?->subject_name;

    $defaultMeta = [
        'date'    => now()->toDateString(),
        'subject' => $subject,
    ];

    return $this->createStudentAlert(
        $enrollment,
        Alert::TYPE_HOMEWORK,
        'تنبيه واجب',
        'لم يكتب الطالب الواجب المنزلي.',
        array_merge($defaultMeta, $meta)
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

    // ========== تنبيهات الموظّفين (للموظّف نفسه) ==========

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


    // ========== المنطق المشترك للطلاب ==========

    private function createStudentAlert(
        Enrollment $enrollment,
        string $type,
        string $title,
        string $desc,
        array $meta
    ): Alert {
        $alert = Alert::create([
            'notifiable_id'   => $enrollment->id,
            'notifiable_type' => Enrollment::class,
            'type'            => $type,
            'audience'        => Alert::AUDIENCE_STUDENT,
            'title'           => $title,
            'description'     => $desc,
            'meta'            => $meta,
        ]);

        $student = $enrollment->student;
        $users   = collect([$student->user, $student->guardian?->user])->filter();

        $this->dispatch($alert, $users);

        return $alert;
    }

    // ========== المنطق المشترك للموظّفين ==========

    private function createStaffAlert(
        Staff $staff,
        string $type,
        string $title,
        string $desc,
        array $meta
    ): Alert {
        $alert = Alert::create([
            'notifiable_id'   => $staff->id,
            'notifiable_type' => Staff::class,
            'type'            => $type,
            'audience'        => Alert::AUDIENCE_STAFF,
            'title'           => $title,
            'description'     => $desc,
            'meta'            => $meta,
        ]);

        $users = collect([$staff->user])->filter();

        $this->dispatch($alert, $users);

        return $alert;
    }

    // ========== الإرسال المركزي ==========

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
                'alert_id'   => (string) $alert->id,
                'alert_type' => $alert->type,
                'audience'   => $alert->audience,
            ]
        );
    }

    // ======= CRUD ==========

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


    public function createManual(array $data): Alert
    {
        if ($data['audience'] === Alert::AUDIENCE_STUDENT) {
            $enrollment = Enrollment::findOrFail($data['enrollment_id']);

            return match ($data['type']) {
                Alert::TYPE_ABSENCE  => $this->createStudentAbsence($enrollment, $data['meta'] ?? []),
                Alert::TYPE_LATE     => $this->createStudentLate($enrollment, $data['meta'] ?? []),
                Alert::TYPE_BEHAVIOR => $this->createStudentBehavior($enrollment, $data['meta'] ?? []),
                Alert::TYPE_PAYMENT  => $this->createStudentPayment($enrollment, $data['meta'] ?? []),
                Alert::TYPE_ESCAPE =>   $this->createStudentEscape($enrollment, $data['meta'] ?? []),
                default              => $this->createStudentAlert(
                    $enrollment,
                    $data['type'],
                    $data['title'],
                    $data['description'] ?? '',
                    $data['meta'] ?? []
                ),
            };
        }

        $staff = Staff::findOrFail($data['staff_id']);

        return match ($data['type']) {
            Alert::TYPE_ABSENCE => $this->createStaffAbsence($staff, $data['meta'] ?? []),
            Alert::TYPE_LATE    => $this->createStaffLate($staff, $data['meta'] ?? []),
            Alert::TYPE_SALARY  => $this->createStaffSalary($staff, $data['meta'] ?? []),
            default             => $this->createStaffAlert(
                $staff,
                $data['type'],
                $data['title'],
                $data['description'] ?? '',
                $data['meta'] ?? []
            ),
        };
    }
    public function deleteAlert(int $id): void
    {
        $alert = Alert::findOrFail($id);
        $alert->delete();
    }
    public function createPaymentAlerts(array $data): Alert
    {
        $enrollment = Enrollment::findOrFail($data['enrollment_id']);

        return match ($data['type']) {
            Alert::TYPE_PAYMENT  => $this->createStudentPayment($enrollment, $data['meta'] ?? []),
            Alert::TYPE_PAYED    => $this->createStudentPayed($enrollment, $data['meta'] ?? []),
            default              => $this->createStudentAlert(
                $enrollment,
                $data['type'],
                $data['title'],
                $data['description'] ?? '',
                $data['meta'] ?? []
            ),
        };
    }
    public function advisorAlerts(array $data): Alert
    {
        $enrollment = Enrollment::findOrFail($data['enrollment_id']);

        return match ($data['type']) {
            Alert::TYPE_ABSENCE  => $this->createStudentAbsence($enrollment, $data['meta'] ?? []),
            Alert::TYPE_LATE     => $this->createStudentLate($enrollment, $data['meta'] ?? []),
            Alert::TYPE_BEHAVIOR => $this->createStudentBehavior($enrollment, $data['meta'] ?? []),
            Alert::TYPE_ESCAPE =>   $this->createStudentEscape($enrollment, $data['meta'] ?? []),
            default              => $this->createStudentAlert(
                $enrollment,
                $data['type'],
                $data['title'],
                $data['description'] ?? '',
                $data['meta'] ?? []
            ),
        };
    }
    public function createStaffAlerts(array $data): Alert
    {
        $staff = Staff::findOrFail($data['staff_id']);

        return match ($data['type']) {
            Alert::TYPE_ABSENCE => $this->createStaffAbsence($staff, $data['meta'] ?? []),
            Alert::TYPE_LATE    => $this->createStaffLate($staff, $data['meta'] ?? []),
            Alert::TYPE_SALARY  => $this->createStaffSalary($staff, $data['meta'] ?? []),
            default             => $this->createStaffAlert(
                $staff,
                $data['type'],
                $data['title'],
                $data['description'] ?? '',
                $data['meta'] ?? []
            ),
        };
    }
}
