<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // هذا السيدر لا ينشئ مستخدم جديد، بل يقوم بإنشاء سجل في جدول الطلاب بناءً على المستخدمين الذين لديهم دور "طالب"
        $students = User::whereHas('role', function($query) {
            $query->where('role_name', 'STUDENT');
        })->get();

        foreach ($students as $student) {
            \App\Models\Student::updateOrCreate(
                ['user_id' => $student->id,'guardian_id' => 1], // نستخدم الـ id الحقيقي للطالب من الحلقة، ونعين guardian_id ثابت (يمكن تعديله لاحقاً)
            );
        }


    }
}
