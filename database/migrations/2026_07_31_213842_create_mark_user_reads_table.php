<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mark_user_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_mark_id')->constrained('student_marks')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['student_mark_id', 'user_id']);
            $table->timestamps();
        });
    }

 
    public function down(): void
    {
        Schema::dropIfExists('mark_user_reads');
    }
};
