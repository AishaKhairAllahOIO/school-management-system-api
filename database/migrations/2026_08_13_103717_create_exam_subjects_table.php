<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('grade_subject_id')->constrained('grade_subjects')->cascadeOnDelete();
            
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('syllabus')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};