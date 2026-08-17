<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportCard\GenerateReportCardRequest;
use App\Http\Requests\Admin\ReportCard\PublishReportCardRequest;
use App\Jobs\GenerateReportCardsJob;
use App\Services\StudentPromotionService;
use App\Models\ReportCard;
use App\Models\AcademicYear;
use App\Http\Resources\ReportCardResource;
use App\ApiResource;
use App\Services\Report\ReportCardGenerationService;
use Illuminate\Http\JsonResponse;
use Throwable;
use App\Services\User\AlertService;

class ReportCardAdminController extends Controller
{
    use ApiResource;

    public function __construct(
        private StudentPromotionService $promotionService,
        private AlertService $alertService
    ) {}

    /**
     * 1. ⚙️ زر إطلاق عملية توليد الجلاءات في الخلفية
     */
   public function generate(GenerateReportCardRequest $request): JsonResponse
{
    try {
        $semesterId = $request->validated('semester_id');
        $maxAllowed = $request->validated('max_allowed_non_failing_failures', 2);

        GenerateReportCardsJob::dispatch($semesterId, $maxAllowed);

        return $this->successResponse(
            null,
            'تم إطلاق عملية توليد الجلاءات في الخلفية بنجاح.'
        );
    } catch (Throwable $e) {
        return $this->errorResponse('حدث خطأ أثناء إطلاق عملية توليد الجلاءات.', 500, ['error' => $e->getMessage()]);
    }
}

    /**
     * 2. 📋 استعراض الجلاءات المولدة للمراجعة من قبل الإدارة قبل نشرها للأهالي
     */
    public function index(): JsonResponse
    {
        try {
            $semesterId = request('semester_id');
            $classRoomId = request('class_room_id');

            $query = ReportCard::with(['details.gradeSubject.subject', 'enrollment.student.user', 'enrollment.classRoom']);

            if ($semesterId) {
                $query->where('semester_id', $semesterId);
            }

            // فلترة لعرض نتائج شعبة معينة فقط إن أراد المدير
            if ($classRoomId) {
                $query->whereHas('enrollment', function ($q) use ($classRoomId) {
                    $q->where('class_room_id', $classRoomId);
                });
            }

            $reportCards = $query->paginate(20);

            return $this->successResponse(
                ReportCardResource::collection($reportCards)->response()->getData(true),
                'تم جلب قائمة الجلاءات بنجاح.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب قائمة الجلاءات.', 500, ['error' => $e->getMessage()]);
        }
    }
    // دالة عرض جلاء معين :


    /**
     * 3. 📢 زر نشر / إلغاء نشر الجلاءات (للمدرسة كاملة أو لشعبة معينة)
     */
    public function togglePublish(PublishReportCardRequest $request): JsonResponse
    {
        try {
            $semesterId  = $request->validated('semester_id');
            $classRoomId = $request->validated('class_room_id');
            $isPublished = $request->validated('is_published');

            $query = ReportCard::where('semester_id', $semesterId);

            // لو حُددت شعبة معينة، ينشر الجلاءات لتلك الشعبة فقط
            if ($classRoomId) {
                $query->whereHas('enrollment', function ($q) use ($classRoomId) {
                    $q->where('class_room_id', $classRoomId);
                });
            }
            $enrollmentsToAlert = collect();
            if ($isPublished) {
                // نجلب فقط الجلاءات التي كانت (غير منشورة) وستصبح (منشورة)
                $enrollmentsToAlert = (clone $query)->where('is_published', false)
                    ->with('enrollment')
                    ->get()
                    ->pluck('enrollment');
            }

            $updatedCount = $query->update(['is_published' => $isPublished]);
            if ($isPublished && $updatedCount > 0 && $enrollmentsToAlert->isNotEmpty()) {
                foreach ($enrollmentsToAlert as $enrollment) {
                    $this->alertService->createPublishReportCardAlart($enrollment, [
                        'semester_id'   => $semesterId,
                        'class_room_id' => $enrollment->class_room_id
                    ]);
                }
            }

            $actionText = $isPublished ? 'نشر' : 'إلغاء نشر';

            return $this->successResponse(
                ['updated_count' => $updatedCount],
                "تمت عملية {$actionText} ({$updatedCount}) جلاء بنجاح."
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء تغيير حالة نشر الجلاءات.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 4. 🎓 زر ترفيع الطلاب الناجحين للعام الجديد (العملية السحرية الذكية)
     * لا تستقبل أي مدخلات من الـ Request، بل تكتشف كل شيء برمجياً.
     */
    public function promote(): JsonResponse
{
    try {
        // 1. اكتشاف العام الحالي النشط تلقائياً
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        if (!$currentYear) {
            return $this->errorResponse('لا يوجد عام دراسي نشط حالياً.');
        }

        // 2. 💡 التعديل الذهبي: جلب أول عام دراسي يبدأ بعد بداية العام الحالي
        $nextYear = AcademicYear::where('start_date', '>', $currentYear->start_date)
                        ->orderBy('start_date', 'asc')
                        ->first();

        if (!$nextYear) {
            return $this->errorResponse('لا يوجد عام دراسي قادم محدد بعد العام الحالي. يرجى إنشاؤه من الإعدادات أولاً.', 400);
        }

        // 3. تنفيذ الترفيع السحري عبر الـ Service
        $result = $this->promotionService->promoteStudents($currentYear->id, $nextYear->id);

        return $this->successResponse(
            $result, 
            'تم ترفيع الطلاب للعام القادم بنجاح وتوليد قيودهم الجديدة.'
        );
    } catch (Throwable $e) {
        return $this->errorResponse('حدث خطأ فادح أثناء عملية ترفيع الطلاب.', 500, ['error' => $e->getMessage()]);
    }
}
public function show($id): JsonResponse
    {
        try {
            // جلب الجلاء مع كل العلاقات المرتبطة به لتجنب مشكلة N+1
            $reportCard = ReportCard::with([
                'details.gradeSubject.subject', 
                'enrollment.student.user', 
                'enrollment.classRoom'
            ])->find($id);

            // التحقق من وجود الجلاء
            if (!$reportCard) {
                return $this->errorResponse('الجلاء المطلوب غير موجود.', 404);
            }

            // إرجاع الجلاء باستخدام الـ Resource المخصص
            return $this->successResponse(
                new ReportCardResource($reportCard),
                'تم جلب تفاصيل الجلاء بنجاح.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب تفاصيل الجلاء.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function getTopStudentsForAdmin(ReportCardGenerationService $topStudentsService): JsonResponse
    {
        try {
            $semesterId = request('semester_id');
            $gradeLevelId = request('grade_level_id'); //اختياري: لتصفية صف معين

            if (!$semesterId) {
                return $this->errorResponse('معرف الفصل الدراسي (semester_id) مطلوب.', 422);
            }

            $topStudents = $topStudentsService->getTopStudentsByGrade($semesterId, $gradeLevelId, 10);

            return $this->successResponse(
                ReportCardResource::collection($topStudents),
                'تم جلب قائمة الطلاب العشرة الأوائل بنجاح.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب قائمة الأوائل.', 500, ['error' => $e->getMessage()]);
        }
    }
}
