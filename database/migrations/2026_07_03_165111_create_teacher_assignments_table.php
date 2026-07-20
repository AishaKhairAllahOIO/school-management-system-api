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
    { {
            Schema::create('teacher_assignments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();

                $table->foreignId('class_room_id')->constrained('class_rooms')->cascadeOnDelete();

                $table->foreignId('grade_subject_id')->constrained('grade_subjects')->cascadeOnDelete();

                $table->timestamps();

                $table->unique(['class_room_id', 'grade_subject_id'], 'unique_class_subject_assignment');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
