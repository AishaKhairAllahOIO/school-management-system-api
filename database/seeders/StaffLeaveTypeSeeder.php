<?php

namespace Database\Seeders;

use App\Models\StaffLeaveType;
use Illuminate\Database\Seeder;

class StaffLeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'إجازة صحية (مرضية)',
                'payment_type' => 'paid', // مدفوعة (لا تُخصم من الراتب)
                'max_days_per_academic_year' => 15,
            ],
            [
                'name' => 'إجازة إدارية (طارئة)',
                'payment_type' => 'paid',
                'max_days_per_academic_year' => 5,
            ],
            [
                'name' => 'إجازة بلا أجر',
                'payment_type' => 'unpaid', // غير مدفوعة (تُخصم من الراتب)
                'max_days_per_academic_year' => 30,
            ],
            [
                'name' => 'إجازة أمومة',
                'payment_type' => 'paid',
                'max_days_per_academic_year' => 90,
            ]
        ];

        foreach ($leaveTypes as $type) {
            StaffLeaveType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}