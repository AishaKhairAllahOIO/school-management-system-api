<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
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
                'meta'        => ['subject' => 'math','date' => '2026-06-01'],
                'created_by'  => 3
            ]
        );

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
                'created_by'  => 9
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
                'created_by'  => 9
            ]
        );

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
                'created_by'  => 9
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
                'created_by'  => 9
            ]
        );

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
                'created_by'  => 10
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
                'created_by'  => 10
            ]
        );


        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 9,
                'type'            => Alert::TYPE_SALARY,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه راتب',
                'description' => 'تم رفع الراتب الشهري يرجى مراجعة حساب شام كاش.',
                'meta'        => [
                    'amount' => 20000,
                    'month' => 'june 2026'
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 9,
                'type'            => Alert::TYPE_ABSENCE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه غياب',
                'description' => 'تم تسجيل غيابك اليوم.',
                'meta'        => [
                    'date' => '2026-06-01'
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 11,
                'type'            => Alert::TYPE_ABSENCE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه غياب',
                'description' => 'تم تسجيل غيابك اليوم.',
                'meta'        => [
                    'date' => '2026-06-01'
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 9,
                'type'            => Alert::TYPE_LATE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه تأخر',
                'description' => 'تم تسجيل تأخرك عن الدوام.',

                'meta'        => [
                    'minutes_late' => 20,
                    'session' => 'الحصة الأولى'
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 11,
                'type'            => Alert::TYPE_LATE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه تأخر',
                'description' => 'تم تسجيل تأخرك عن الدوام.',

                'meta'        => [
                    'minutes_late' => 20,
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 3,
                'type'            => Alert::TYPE_LATE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه تأخر',
                'description' => 'تم تسجيل تأخرك عن الدوام.',

                'meta'        => [
                    'minutes_late' => 20,
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 3,
                'type'            => Alert::TYPE_SALARY,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه راتب',
                'description' => 'تم رفع الراتب الشهري يرجى مراجعة حساب شام كاش.',

                'meta'        => [
                    'amount' => 20000,
                    'month' => 'june 2026'
                ],
                'created_by'  => 9
            ]
        );
        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 11,
                'type'            => Alert::TYPE_SALARY,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'تنبيه راتب',
                'description' => 'تم رفع الراتب الشهري يرجى مراجعة حساب شام كاش.',

                'meta'        => [
                    'amount' => 20000,
                    'month' => 'june 2026'
                ],
                'created_by'  => 9
            ]
        );

        Alert::updateOrCreate(
            [
                'notifiable_type' => Staff::class,
                'notifiable_id'   => 11,
                'type'            => Alert::TYPE_SYSTEM_NOTICE,
            ],
            [
                'audience'    => Alert::AUDIENCE_STAFF,
                'title'       => 'قوائم الفصل جاهزة للمراجعة',
                'description' => 'يرجى مراجعة قائمة الفصل الخاصة بالطلاب في نهاية الفصل الدراسي والتأكيد',
                'meta'        => ['action' => 'مراجعة قائمة الفصل.'],
                'created_by'  => 1
            ]
        );
    }
}
