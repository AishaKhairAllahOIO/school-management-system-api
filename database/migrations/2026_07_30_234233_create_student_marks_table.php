<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();

            $table->foreignId('assessment_component_id')->constrained('assessment_components')->cascadeOnDelete();

            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('mark', 8, 2)->nullable();

            $table->string('notes')->nullable();

            $table->timestamps();

            $table->unique(['enrollment_id', 'assessment_component_id'], 'student_assessment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
