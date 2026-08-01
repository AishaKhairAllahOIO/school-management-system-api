<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_quiz_attempt_id')->constrained('student_quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('options')->cascadeOnDelete();

            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_quiz_attempt_answers');
    }
};
