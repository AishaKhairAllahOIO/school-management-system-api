<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_user_reads', function (Blueprint $table) {
            $table->foreignId('class_student_evaluation_id')
                ->constrained('class_student_evaluations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('read_at')->useCurrent();

            $table->primary(['class_student_evaluation_id', 'user_id'], 'eval_user_read_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_user_reads');
    }
};
