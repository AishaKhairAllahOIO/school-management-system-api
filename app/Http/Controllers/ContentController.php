<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Staff;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Cache;

class ContentController extends Controller
{
    /**
     * Get all website content.
     */
public function index()
{
    $contents = Content::query()
        ->orderBy('key')
        ->pluck('value', 'key')
        ->toArray();

    $result = [];

    foreach ($contents as $key => $value) {
        \Illuminate\Support\Arr::set(
            $result,
            $key,
            $value
        );
    }

    return response()->json($result);
}

    /**
     * Create or update website content.
     *
     * If the key exists -> update.
     * If the key does not exist -> create.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        $content = Content::updateOrCreate(
            [
                'key' => $validated['key'],
            ],
            [
                'value' => $validated['value'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Content saved successfully.',
            'content' => $content,
        ]);
    }
    public function getPublicStats(): array
    {
        // استخدام الكاش لمدة 24 ساعة لتخفيف الضغط عن السيرفر
        return Cache::remember('website_public_stats', 60 * 60 * 24, function () {
            
            // 1️⃣ إجمالي عدد الطلاب (المسجلين رسمياً والمنتهين، مع استبعاد المعلقين Suspended)
            $totalStudents = Enrollment::whereIn('enrollment_status', ['enrolled', 'completed'])
                ->distinct('student_id')
                ->count('student_id');

            // 2️⃣ إجمالي الموظفين (مع استبعاد المدراء)
            $totalStaff = Staff::whereHas('user.roles', function ($query) {
                $query->whereNotIn('name', ['super_admin']);
            })->count();

            // 3️⃣ حساب نسبة النجاح الكلية للمدرسة بناءً على (القيود التي تم تقييمها فعلياً)
            // نستثني الـ under_study والـ null لكي لا تنكسر النسبة في بداية العام الدراسي
            $evaluatedEnrollments = Enrollment::whereIn('academic_result', ['passed', 'failed'])->count();
            $passedEnrollments = Enrollment::where('academic_result', 'passed')->count();
            
            // حساب النسبة المئوية مع تجنب القسمة على صفر
            $overallPassRate = $evaluatedEnrollments > 0 
                ? round(($passedEnrollments / $evaluatedEnrollments) * 100, 1) 
                : 0;

            // 💡 ملاحظة: إذا أردتِ حساب نسبة النجاح بناءً على (الجلاءات المنشورة) بدلاً من القيود، 
            // يمكنك استخدام هذا الكود البديل:
            /*
            $evaluatedCards = ReportCard::where('is_published', true)->count();
            $passedCards = ReportCard::where('is_published', true)->where('final_result', 'passed')->count();
            $overallPassRate = $evaluatedCards > 0 ? round(($passedCards / $evaluatedCards) * 100, 1) : 0;
            */

            // 4️⃣ تشكيل النتيجة النهائية
            return [
                'total_students'    => $totalStudents,
                'total_staff'       => $totalStaff,
                'overall_pass_rate' => $overallPassRate,
            ];
        });
    }
}