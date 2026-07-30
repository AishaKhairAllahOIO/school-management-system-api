<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('grade_level_id')->constrained('grade_levels')->restrictOnDelete();
            $table->foreignId('class_room_id')->nullable()->constrained('class_rooms')->nullOnDelete();

            $table->enum('enrollment_status', ['suspended', 'enrolled', 'completed'])->default('suspended');

            $table->enum('academic_result', ['under_study', 'passed', 'failed'])->nullable()->default(null);

            $table->date('enrollment_date')->nullable()->default(null); // تاريخ الدفع وتفعيل التسجيل
            $table->timestamp('completed_at')->nullable()->default(null); // تاريخ تبرئة الذمة المالية والنجاح

            $table->timestamps(); // ينشئ تلقائياً created_at و updated_at
            $table->softDeletes();

            // القفل الفريد القياسي
            $table->unique(['student_id', 'academic_year_id'], 'unique_student_per_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
