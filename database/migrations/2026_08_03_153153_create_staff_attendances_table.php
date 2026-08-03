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
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status', 20); // 'present', 'absent', 'on_leave', 'partial_absence'
            $table->string('absence_type', 20)->nullable(); // 'excused', 'unexcused'
            
            $table->foreignId('staff_leave_id')
                ->nullable()
                ->constrained('staff_leaves')
                ->nullOnDelete();
                
            $table->timestamps();

            $table->unique(['staff_id', 'attendance_date'], 'staff_attendance_daily_unique');
            $table->index(['attendance_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
