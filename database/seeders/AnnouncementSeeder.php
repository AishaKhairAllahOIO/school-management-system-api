<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Announcement::updateOrCreate(
            ['title' => 'اجتماع الهيئة التدريسية والإدارية'],
            [
                'audience'    => Announcement::AUDIENCE_STAFF,
                'description' => 'يرجى حضور الاجتماع الشهري لمناقشة خطة الشهر القادم وذلك يوم الخميس الساعة 10 صباحاً في قاعة الاجتماعات الرئيسية.',
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'تحديث على جدول امتحانات منتصف الفصل'],
            [
                'audience'    => Announcement::AUDIENCE_STUDENT,
                'description' => 'أعزاءنا الطلاب، يرجى العلم بأنه تم تعديل جدول الامتحانات، يمكنكم الآن مراجعة الجداول المحدثة من خلال التطبيق المدرسي.',
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'عطلة رسمية بمناسبة الأعياد'],
            [
                'audience'    => Announcement::AUDIENCE_BOTH,
                'description' => 'بناءً على القرار الوزاري، تعطل المدرسة أبوابها يوم الأحد القادم بمناسبة الأعياد الوطنية، وكل عام وأنتم بخير.',
            ]
        );
    }
}
