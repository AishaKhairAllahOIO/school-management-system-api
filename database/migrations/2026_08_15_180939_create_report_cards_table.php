<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            
            $table->decimal('total_marks', 8, 2)->default(0); // ما حصل عليه الطالب
            $table->decimal('max_total_marks', 8, 2)->default(0); // المجموع الأعظمي لكل المواد
            
            $table->enum('attendance_status', ['passed', 'failed'])->default('passed'); // هل سقط بالغياب؟
            $table->enum('final_result', ['passed', 'failed']); // النتيجة النهائية
            
            $table->json('failure_reasons')->nullable(); 
            
            $table->boolean('is_published')->default(false); 
            
            $table->timestamps();
            
            $table->unique(['enrollment_id', 'semester_id'], 'enrollment_semester_report_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};