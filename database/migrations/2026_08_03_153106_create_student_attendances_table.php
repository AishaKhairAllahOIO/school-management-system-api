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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('class_room_id')->constrained('class_rooms')->restrictOnDelete();
            
            $table->date('attendance_date');
            $table->string('status', 20); // 'present', 'absent'
            $table->string('absence_type', 20)->nullable(); // 'excused', 'unexcused'
            $table->timestamps();

            $table->unique(['enrollment_id', 'attendance_date'], 'student_attendance_daily_unique');
            $table->index(['semester_id', 'class_room_id', 'attendance_date'], 'idx_std_att_search');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
