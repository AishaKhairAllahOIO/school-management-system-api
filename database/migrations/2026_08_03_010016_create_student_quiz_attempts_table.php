<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_quiz_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('practice_quiz_id')->constrained('practice_quizzes')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->decimal('total_mark', 8, 2);
            $table->decimal('earned_mark', 8, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_quiz_attempts');
    }
};
