<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\FinancialAccount;
use App\Models\ScheduledInstallment;
use App\Models\PaymentTransaction;
use App\Models\FeePlan;
use App\Models\InstallmentPolicy;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\ClassRoom;
use App\Models\AssessmentComponent;
use App\Models\StudentMark;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MasterStudentSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب البيانات الأساسية المتوفرة في النظام مسبقاً
        $academicYear = AcademicYear::where('is_current', true)->first() 
            ?? AcademicYear::first();

        if (!$academicYear) {
            $this->command->error('❌ خطأ: يرجى تشغيل AcademicYearSeeder أولاً!');
            return;
        }

        $gradeLevel = GradeLevel::first();
        $classRoom = ClassRoom::first();
        
        $feePlan = FeePlan::first() ?? FeePlan::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id'   => $gradeLevel?->id ?? 1,
            'name'             => 'الخطة المالية العامة للصفوف',
            'base_amount'      => 600000.00
        ]);

        $policy = InstallmentPolicy::first() ?? InstallmentPolicy::create([
            'name'               => 'تقسيط على 3 دفعات',
            'installments_count' => 3
        ]);

        $components = AssessmentComponent::all();

        // قوائم أسماء عربية حقيقية ومتنوعة لتوليد بيانات واقعية
        $firstNames = ['محمد', 'أحمد', 'عمر', 'علي', 'حسين', 'إبراهيم', 'يوسف', 'خالد', 'عبد الرحمن', 'حمزة', 'فاطمة', 'مريم', 'عائشة', 'زينب', 'سارة', 'نور', 'راما', 'لين', 'هبة', 'تولين', 'يحيى', 'بلال', 'سيف', 'آية', 'بتول'];
        $lastNames  = ['الخالد', 'العلي', 'السعيد', 'المحمود', 'الخطيب', 'الرفاعي', 'الداوود', 'الحسن', 'النايف', 'الشامي', 'النجار', 'الحوراني'];
        $places     = ['دمشق', 'حلب', 'حمص', 'حماة', 'اللاذقية', 'طرطوس', 'درعا', 'دير الزور'];

        $this->command->info('🚀 جاري توليد بيانات 25 طالباً مع كافة تفاصيلهم المالية والأكاديمية...');

        DB::transaction(function () use ($academicYear, $gradeLevel, $classRoom, $feePlan, $policy, $components, $firstNames, $lastNames, $places) {
            
for ($i = 1; $i <= 25; $i++) {
                
                // 2. إنشاء مستخدم لولي الأمر (Guardian User)
                $gFirstName = $firstNames[array_rand($firstNames)];
                $gLastName  = $lastNames[array_rand($lastNames)];
                
                $guardianUser = User::create([
                    'first_name'     => $gFirstName,
                    'last_name'      => $gLastName,
                    'father_name'    => 'مصطفى',
                    'mother_name'    => 'ابتسام',
                    'birth_date'     => '1975-04-12',
                    'birth_place'    => $places[array_rand($places)],
                    'gender'         => 'male',
                    'phone_number'   => '09' . rand(11111111, 99999999),
                    'email'          => "guardian.test{$i}@school.test",
                    'password'       => Hash::make('password'),
                    'address'        => 'دمشق، المزة',
                    'record_status'  => 'active',
                    'account_status' => 'enabled',
                    'photo_url'      => 'default.png',
                ]);

                $guardian = Guardian::create([
                    'user_id' => $guardianUser->id
                ]);

                // 3. إنشاء مستخدم للطالب (Student User)
                $sFirstName = $firstNames[array_rand($firstNames)];
                $gender = ($i % 2 == 0) ? 'female' : 'male';

                $studentUser = User::create([
                    'first_name'     => $sFirstName,
                    'last_name'      => $gLastName,
                    'father_name'    => $gFirstName,
                    'mother_name'    => 'هدى',
                    'birth_date'     => '2010-' . rand(1, 12) . '-' . rand(1, 28),
                    'birth_place'    => $places[array_rand($places)],
                    'gender'         => $gender,
                    'phone_number'   => '09' . rand(11111111, 99999999),
                    'email'          => "student.test{$i}@school.test",
                    'password'       => Hash::make('password'),
                    'address'        => 'دمشق، المزة',
                    'record_status'  => 'active',
                    'account_status' => 'enabled',
                    'photo_url'      => 'default.png',
                ]);

                $student = Student::create([
                    'user_id'     => $studentUser->id,
                    'guardian_id' => $guardian->id
                ]);

                // 💡 4. تحديد الحالة المالية وحالة التسجيل بناءً على قاعدة متسقة وصارمة
            if ($i <= 13) {
                $paymentStatus    = 'fully_paid';
                $enrollmentStatus = 'completed'; 
                $paidBalance      = 600000.00;
            } else {
                $paymentStatus    = 'partially_paid';
                $enrollmentStatus = 'enrolled';   
                $paidBalance      = 300000.00;
            }

                $totalAmount = 600000.00;
                $remainingBalance = $totalAmount - $paidBalance;

                // إنشاء القيد الأكاديمي (Enrollment)
                $enrollment = Enrollment::create([
                    'student_id'        => $student->id,
                    'academic_year_id'  => $academicYear->id,
                    'grade_level_id'    => $gradeLevel?->id ?? 1,
                    'class_room_id'     => $classRoom?->id,
                    'enrollment_status' => $enrollmentStatus,
                    'academic_result'   => 'under_study',
                    'enrollment_date'   => ($paidBalance > 0) ? now()->subDays(rand(10, 50)) : null,
                    'completed_at'      => ($enrollmentStatus === 'completed') ? now() : null,
                ]);

                // 5. إنشاء الحساب المالي (Financial Account)
                $financialAccount = FinancialAccount::create([
                    'student_id'            => $student->id,
                    'academic_year_id'      => $academicYear->id,
                    'fee_plan_id'           => $feePlan->id,
                    'installment_policy_id' => $policy->id,
                    'total_required_amount' => $totalAmount,
                    'remaining_balance'     => $remainingBalance,
                    'payment_status'        => $paymentStatus,
                ]);

                // 6. توزيع الأقساط المجدولة (3 أقساط لكل طالب)
                $installmentAmount = $totalAmount / 3;
                for ($instNum = 1; $instNum <= 3; $instNum++) {
                    $instPaid = 0.00;
                    $instStatus = 'pending';

                    if ($paymentStatus === 'fully_paid') {
                        $instPaid = $installmentAmount;
                        $instStatus = 'paid';
                    } elseif ($paymentStatus === 'partially_paid' && $instNum === 1) {
                        $instPaid = $installmentAmount;
                        $instStatus = 'paid';
                    }

                    ScheduledInstallment::create([
                        'financial_account_id' => $financialAccount->id,
                        'installment_number'   => $instNum,
                        'title'                => "القسط الدراسي رقم {$instNum}",
                        'amount_due'           => $installmentAmount,
                        'amount_paid'          => $instPaid,
                        'due_date'             => now()->addMonths($instNum)->format('Y-m-d'),
                        'status'               => $instStatus,
                    ]);
                }

                // 7. إنشاء حركة دفع مالية إن سدد شيئاً
                if ($paidBalance > 0) {
                    PaymentTransaction::create([
                        'financial_account_id'   => $financialAccount->id,
                        'paid_amount'            => $paidBalance,
                        'payment_method'         => ($i % 2 == 0) ? 'cash' : 'bank_transfer',
                        'paper_receipt_no'       => 'REC-' . rand(100000, 999999),
                        'collected_by_user_id'   => 1,
                    ]);
                }

                // 8. حقن علامات المواد (Student Marks)
                if ($components->isNotEmpty()) {
                    foreach ($components as $comp) {
                        $multiplier = match(true) {
                            ($i <= 5)  => 0.90, 
                            ($i <= 20) => 0.70, 
                            default    => 0.40, 
                        };

                        StudentMark::create([
                            'enrollment_id'           => $enrollment->id,
                            'assessment_component_id' => $comp->id,
                            'teacher_id'              => 1,
                            'mark'                    => round($comp->max_mark * $multiplier, 2),
                        ]);
                    }
                }

                // 9. تسجيل حضور وغياب الطالب
                for ($d = 1; $d <= 10; $d++) {
                    $isAbsent = ($i === 3 && $d <= 8); 

                    StudentAttendance::create([
                        'enrollment_id'   => $enrollment->id,
                        'semester_id'     => 1,
                        'class_room_id'   => $classRoom?->id,
                        'attendance_date' => now()->subDays($d)->format('Y-m-d'),
                        'status'          => $isAbsent ? 'absent' : 'present',
                        'absence_type'    => $isAbsent ? 'unexcused' : null,
                    ]);
                }
            }
        });

        $this->command->info('🎉 تم بنجاح إنشاء 25 طالباً مع كافة سجلاتهم المرتبطة بالكامل!');
    }
}