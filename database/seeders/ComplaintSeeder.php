<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'الشؤون الأكاديمية والتعليمية' => [
                ['title' => 'تراجع ملحوظ ومفاجئ في مستوى الطالب الدراسي.', 'severity' => 'high'],
                ['title' => 'معاقبة الطالب بإنقاص درجاته لأسباب غير أكاديمية.', 'severity' => 'high'],
                ['title' => 'عدم تصحيح الدفاتر والواجبات المدرسية بشكل دوري.', 'severity' => 'medium'],
                ['title' => 'صعوبة المنهج أو عدم وضوح شرح المعلم داخل الصف.', 'severity' => 'medium'],
            ],
            'الشؤون السلوكية والانضباط' => [
                ['title' => 'تعرض الطالب للتنمر اللفظي أو الجسدي من قبل زملائه.', 'severity' => 'high'],
                ['title' => 'سلوك غير لائق من أحد المعلمين أو الموظفين.', 'severity' => 'high'],
                ['title' => 'تعرض الطالب لسرقة أو فقدان متكرر لأغراضه المدرسية.', 'severity' => 'medium'],
            ],
            'النقل والمواصلات' => [
                ['title' => 'قيادة الحافلة بتهور أو سرعة زائدة.', 'severity' => 'high'],
                ['title' => 'تأخر الحافلة المدرسية المستمر عن الموعد المحدد صباحاً.', 'severity' => 'medium'],
                ['title' => 'سلوك غير لائق من قبل سائق الحافلة المدرسية.', 'severity' => 'high'],
            ],
            'المرافق والخدمات' => [
                ['title' => 'مستوى النظافة في دورات المياه غير مقبول.', 'severity' => 'high'],
                ['title' => 'الأطعمة المقدمة في المقصف غير صحية أو غالية الثمن.', 'severity' => 'medium'],
                ['title' => 'عدم توفر تهوية أو تكييف جيد داخل الغرفة الصفية.', 'severity' => 'medium'],
            ],
            'الإدارة والتواصل' => [
                ['title' => 'وجود خطأ في البيانات المالية أو الأقساط المدرسية.', 'severity' => 'high'],
                ['title' => 'صعوبة التواصل مع الإدارة أو تأخر الرد على الاستفسارات.', 'severity' => 'medium'],
                ['title' => 'أخطاء في بيانات الطالب الرسمية أو اسمه في الشهادات.', 'severity' => 'low'],
            ],
        ];

        foreach ($data as $categoryName => $types) {
            $category = ComplaintCategory::create([
                'name' => $categoryName,
                'is_active' => true,
            ]);

            foreach ($types as $type) {
                $category->types()->create([
                    'title' => $type['title'],
                    'severity' => $type['severity'],
                    'is_active' => true,
                ]);
            }
        }


        Complaint::updateOrCreate([
                'guardian_id'=> 1,
                'student_id'=> 1,
                'academic_year_id'=>1,
                'semester_id'=>1,
                'complaint_type_id'=>4
        ]);
        Complaint::updateOrCreate([
                'guardian_id'=> 1,
                'student_id'=> 2,
                'academic_year_id'=>1,
                'semester_id'=>1,
                'complaint_type_id'=>3
        ]);
    }
}