<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        // تنبيه واجب
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_HOMEWORK,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه واجب',
                'description' => 'لم يكتب الطالب الواجب المنزلي.',
                'meta'        => ['subject_id' => 1],
                'created_by'  => 3
            ]
        );

        // تنبيه غياب
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_ABSENCE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه غياب',
                'description' => 'تم تسجيل غياب الطالب اليوم.',
                'meta'        => ['date' => '2026-06-01'],
            ]
        );

        // تنبيه سلوك
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_BEHAVIOR,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه سلوك',
                'description' => 'تم تسجيل ملاحظة سلوكية للطالب.',
                'meta'        => ['severity' => 'high'],
            ]
        );

        // تنبيه تأخير
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_LATE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه تأخير',
                'description' => 'تم تسجيل تأخر الطالب عن الدوام.',
                'meta'        => ['minutes_late' => 15, 'session' => 'Math Class'],
            ]
        );

        // تنبيه هروب
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_ESCAPE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه هروب',
                'description' => 'تم تسجيل حالة هروب الطالب.',
                'meta'        => ['session' => 'Science Class'],
            ]
        );

        // تنبيه دفع
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_PAYMENT,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه دفع',
                'description' => 'يوجد تأخر في دفع قسط.',
                'meta'        => ['amount_due' => 2000, 'due_date' => '2026-06-15'],
            ]
        );

        // تنبيه تسديد
        Alert::updateOrCreate(
            [
                'notifiable_type' => Enrollment::class,
                'notifiable_id'   => 1,
                'type'            => Alert::TYPE_PAYED,
            ],
            [
                'audience'    => Alert::AUDIENCE_STUDENT,
                'title'       => 'تنبيه دفع',
                'description' => 'تم تسديد دفعة من القسط.',
                'meta'        => ['amount' => 2000],
            ]
        );
    }
}
