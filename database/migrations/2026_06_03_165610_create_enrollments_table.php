<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
       // 1. الطالب يحذف تسجيعه لو حُذف (منطقية)
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            
            // 2. [تصحيح سيادي]: منع حذف سنة أو صف تحته طلاب مسجلين
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('grade_level_id')->constrained('grade_levels')->restrictOnDelete();
            
            // 3. [تصحيح سيادي]: جعل الشعبة Nullable لأن التوزيع يتم بعد القبض
            $table->foreignId('class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();

            // 4. حالات التسجيل (pending = بانتظار المالية | confirmed = تم التفعيل)
            $table->enum('enrollment_status', ['pending', 'confirmed', 'suspended', 'withdrawn'])->default('pending');   
            
            $table->enum('academic_result', ['under_study', 'passed', 'failed'])->nullable()->default(null);            
            $table->timestamps();
            $table->softDeletes(); // [إضافة حاسمة لحفظ التاريخ]

            // 5. [تصحيح سيادي]: القفل الميكانيكي لمنع تكرار تسجيل نفس الطالب في نفس السنة
            $table->unique(['student_id', 'academic_year_id'], 'unique_student_per_year');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
