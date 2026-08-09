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
        Schema::create('homework_user_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')
                  ->constrained('homeworks')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('student_id')
                  ->nullable()
                  ->constrained('students')
                  ->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['homework_id', 'user_id', 'student_id'], 'homework_user_reads_unique');

            $table->index(['user_id', 'read_at']);
            $table->index(['student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_user_reads');
    }
};
