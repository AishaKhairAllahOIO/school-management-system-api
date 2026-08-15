<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained('report_cards')->cascadeOnDelete();
            $table->foreignId('grade_subject_id')->constrained('grade_subjects')->cascadeOnDelete();
            
            $table->json('evaluations_summary'); 
            
            $table->decimal('subject_total', 8, 2)->default(0); // مجموع الطالب في هذه المادة
            $table->decimal('passing_mark', 8, 2); // لقطة ثابتة لدرجة النجاح وقت التوليد
            $table->decimal('max_mark', 8, 2); // لقطة ثابتة للدرجة العظمى
            
            $table->boolean('is_failing_subject')->default(false); // هل هي مادة مرسبة؟
            $table->enum('status', ['passed', 'failed']); // هل نجح في هذه المادة؟
            
            $table->timestamps();
            
            $table->unique(['report_card_id', 'grade_subject_id'], 'report_card_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_details');
    }
};