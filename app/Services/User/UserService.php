<?php

namespace App\Services\User;


use App\ApiResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\Semester;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class UserService
{

    use ApiResource;

   /* public function getStudentDashboard($user)
    {
        $student = $user->student;
        if (!$student) {
            throw new HttpResponseException($this->errorResponse('هذا الحساب غير مسجل كطالب في النظام.', 404));
        }

        // 2. جلب التسجيل الأكاديمي النشط بناءً على تاريخ اليوم الحالي
        $currentEnrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->with(['gradeLevel', 'classRoom']) // تم استخدام gradLevel كما هو معرف في الموديل الخاص بكِ
            ->first();

        if (!$currentEnrollment) {
            throw new HttpResponseException($this->errorResponse('لا يوجد تسجيل أكاديمي نشط للطالب في السنة الدراسية الحالية.', 404));
        }

        // 3. جلب الفصل الدراسي (الترم) الحالي التابع لهذه السنة الدراسية
        $semester = \App\Models\Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        // الاحتياط: إذا لم تتطابق التواريخ تماماً مع الترم الحالي، يتم جلب أول ترم متاح في السنة
        if (!$semester) {
            $semester = \App\Models\Semester::where('academic_year_id', $currentEnrollment->academic_year_id)->first();
        }

        if (!$semester) {
            throw new HttpResponseException($this->errorResponse('لا يوجد فصل دراسي معرف لهذه السنة الأكاديمية.', 404));
        }

        // 4. تحديد يوم الغد باللغة الإنجليزية (مثل: Saturday, Sunday...) لمطابقته مع جدول الأيام
        $tomorrow = Carbon::tomorrow()->format('l');

        // 5. الاستعلام المباشر لجدول الحصص الأسبوعي لفلترة حصص الغد للطالب
        $scheduleSlots = \App\Models\ScheduleTimeSlot::with(['timeSlot', 'subject'])
            ->where('semester_id', $semester->id)
            ->where('grade_level_id', $currentEnrollment->grade_level_id)
            // إذا قمتِ بربط الـ enrollment بالشعبة (class_room_id) فكي التعليق عن السطر التالي لفلترة أدق لحصص شعبته فقط:
            // ->where('class_room_id', $currentEnrollment->class_room_id)
            ->whereHas('day', function ($query) use ($tomorrow) {
                // البحث عن اليوم سواء كان مخزناً بالاسم الكامل أو الاختصار
                $query->where('day_name', $tomorrow);
            })
            ->get();

        // 6. تنسيق البيانات النهائية لإرسالها للـ Frontend
        return [
            // إرجاع التوكن ليتمكن التطبيق من حفظه واستخدامه في الطلبات القادمة
            'personal_info' => [
                new UserResource($user)
            ],
            'academic_info' => [
                'grade_name'    => $currentEnrollment->gradeLevel->grade_name ?? 'غير محدد',
                'semester_name' => $semester->semester_name ?? 'غير محدد',
                'class_number'  => $currentEnrollment->classRoom->class_number ?? 'غير محدد',
            ],
            'tomorrow_schedule' => $scheduleSlots->map(function ($slot) {
                return [
                    // الوصول للبيانات عن طريق العلاقات المرتبطة بالجدول الوسيط
                    'slot_name'    => $slot->timeSlot->slot_name ?? 'غير محدد',
                    'subject_name' => $slot->subject->subject_name ?? 'غير محدد',
                    'start_time'   => $slot->timeSlot->start_time ?? '',
                    'end_time'     => $slot->timeSlot->end_time ?? '',
                ];
            })->values()->toArray()

        ];
    }

    */


    public function getStudent($user)
    {
        $student = $user->student;

        if (!$student) {
            throw new HttpResponseException($this->errorResponse('هذا الحساب غير مسجل كطالب في النظام.', 404));
        }

        $currentEnrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->with(['gradeLevel', 'classRoom'])
            ->first();

        if (!$currentEnrollment) {
            throw new HttpResponseException($this->errorResponse('لا يوجد تسجيل أكاديمي نشط للطالب في السنة الدراسية الحالية.', 404));
        }

        $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$semester) {
            $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)->first();
        }

        if (!$semester) {
            throw new HttpResponseException($this->errorResponse('لا يوجد فصل دراسي معرف لهذه السنة الأكاديمية.', 404));
        }

        return [
            'academic_info' =>
            [
                'grade_name'    => $currentEnrollment->gradeLevel->name ?? 'غير محدد',
                'semester_name' => $semester->semester_name ?? 'غير محدد',
                'name'  => $currentEnrollment->classRoom->name ?? 'غير محدد',
            ],
        ];
    }

    public function getGuardian($user)
    {
        $guardian = $user->guardian;

        if (!$guardian) {
            throw new HttpResponseException($this->errorResponse('هذا الحساب غير مسجل كولي أمر في النظام.', 404));
        }

        $students = $guardian->students()->with(['enrollments' => function ($q) {
            $q->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })->with(['gradeLevel', 'classRoom']);
        }])->get();

        $childrenCards = $students->map(function ($student) {
        $currentEnrollment = $student->enrollments->first();

            $gradeName = 'غير مسجل حالياً';
            $className = 'غير محدد';
            $semesterName = 'غير محدد';

            if ($currentEnrollment) {
                $gradeName = $currentEnrollment->gradeLevel->name ?? 'غير محدد';
                $className = $currentEnrollment->classRoom->name ?? 'غير محدد';

                $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                $semesterName = $semester->semester_name ?? 'غير محدد';
            }

            'user' . 'student';

            // إرجاع بيانات البطاقة الواحدة مسطحة (Flat) لتسهيل عرضها في الفرونت إند
            return [
                'id' => $student->id,
                'first_name'      => $student->user->first_name,
                'father_name' => $student->user->father_name,
                'last_name' => $student->user->last_name,
                'gender' => $student->user->gender,
                'student_personal_photo' => url('api/user/photos/' . $student->user->personal_photo),
                'grade_name'        => $gradeName,
                'class_room_name'      => $className,
            ];
        })->toArray();

        return [
            'children_cards' => $childrenCards
        ];
    }
        public function getRoleCounts(): array
    {
        $roles = ['teacher', 'adviser', 'counselor', 'secretary', 'service_staff', 'super_admin','student'];
        $counts = [];

        foreach ($roles as $role) {
            $counts[$role] = User::role($role)->count();
        }
        $counts['total'] = User::role($roles)->count();

        return $counts;
    }


    public function getUsersByRole(string $roleName, int $perPage = 15)
    {
        return User::role($roleName)
            ->with(['roles']) // جلب علاقة staff إذا كان لديه تفاصيل موظف
            ->paginate($perPage);
    }



    // public function getAuthenticatedProfile(User $user)
    // {

    //     // 1. نسحب اسم الرتبة الأولى للمستخدم من Spatie
    //     $role = $user->getRoleNames()->first() ?? 'standard';

    //     $relations = match ($role) {
    //         // 'student'  => ['student'],
    //         // 'guardian' => ['guardian'],
    //         'teacher', 'secretary', 'adviser', 'counselor', 'service_staff','super_admin' => ['staff'],
    //         default    => [],
    //     };

    //     return $user->loadMissing($relations);
    // }
    // public function updateStaffRecord(User $user, array $data): User
    // {
    //     return DB::transaction(function () use ($user, $data) {

    //         $userData  = Arr::except($data, ['degree', 'specialization', 'university', 'graduation_year', 'experience_years']);
    //         $staffData = Arr::only($data, ['degree', 'specialization', 'university', 'graduation_year', 'experience_years']);

    //         if (!empty($userData)) {
    //             $user->update($userData);
    //         }

    //         if (!empty($staffData)) {
    //             $user->staff()->updateOrCreate(
    //                 ['user_id' => $user->id],
    //                 $staffData
    //             );
    //         }

    //         return $user->fresh(['staff']);
    //     });
    // }




public function updateProfileImage(User $user, $imageFile): User
    {
        if ($user->personal_photo && Storage::disk('local')->exists($user->personal_photo)) {
            Storage::disk('local')->delete($user->personal_photo);
        }

        $imagePath = $imageFile->store('personal_photos', 'local');

        $user->update([
            'personal_photo' => $imagePath,
        ]);

        return $user->fresh();
    }
}



