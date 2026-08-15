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
        Schema::create('teacher_period_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_attendance_id')
                ->constrained('staff_attendances')
                ->cascadeOnDelete();
                
            $table->foreignId('schedule_entry_id')->constrained('schedule_entries')->cascadeOnDelete();
                
            $table->timestamps();

             $table->unique(['staff_attendance_id', 'schedule_entry_id'], 'unq_teacher_period_att');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_period_attendances');
    }
};
