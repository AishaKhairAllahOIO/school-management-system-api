<?php

namespace App\Services\User;


use App\ApiResource;
use App\Http\Resources\Auth\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class UserService
{

    use ApiResource;

    public function getStudentDashboard($user)
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

    public function getGuardianDashboard($user)
    {
        $guardian = $user->guardian;
        if (!$guardian) {
            throw new HttpResponseException($this->errorResponse('هذا الحساب غير مسجل كولي أمر في النظام.', 404));
        }

        // 1. جلب الأبناء المرتبطين بولي الأمر
        $students = $guardian->students()->with(['enrollments' => function ($q) {
            $q->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })->with(['gradeLevel', 'classRoom']);
        }])->get();

        // 2. بناء بيانات البطاقات (Cards) لكل ابن
        $childrenCards = $students->map(function ($student) {
            $currentEnrollment = $student->enrollments->first();

            // قيم افتراضية في حال كان الابن غير مسجل في السنة الحالية
            $gradeName = 'غير مسجل حالياً';
            $className = 'غير محدد';
            $semesterName = 'غير محدد';

            // إذا كان لديه تسجيل نشط، نقوم بجلب البيانات
            if ($currentEnrollment) {
                $gradeName = $currentEnrollment->gradeLevel->grade_name ?? 'غير محدد';
                $className = $currentEnrollment->classRoom->class_number ?? 'غير محدد';

                $semester = \App\Models\Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                $semesterName = $semester->semester_name ?? 'غير محدد';
            }

            'user'.'student';

            // إرجاع بيانات البطاقة الواحدة مسطحة (Flat) لتسهيل عرضها في الفرونت إند
            return [
                // ملاحظة: إذا كان الاسم والصورة في جدول users يجب كتابتها $student->user->first_name
                'student_name'      => $student->user->first_name .' '. $student->user->father_name . ' ' . $student->user->last_name,
                'student_photo_url' => $student->user->photo_url ?? null,
                'grade_name'        => $gradeName,
                'class_number'      => $className,
            ];
        })->toArray();

        return [
            'personal_info' => [
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'role'       => 'Guardian',
            ],
            'children_cards' => $childrenCards
        ];
    }
}
