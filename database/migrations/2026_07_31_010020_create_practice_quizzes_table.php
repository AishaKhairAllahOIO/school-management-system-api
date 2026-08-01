<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grade_subject_id')->constrained('grade_subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('staff')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_quizzes');
    }
};
