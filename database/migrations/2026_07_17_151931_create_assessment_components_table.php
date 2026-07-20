<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grade_subject_id')->constrained('grade_subjects')->cascadeOnDelete();
            $table->enum('type', ['oral', 'homework', 'quiz1', 'quiz2', 'exam', 'participation']);
            $table->string('name');
            $table->decimal('max_mark', 8, 2);
            $table->decimal('weight_percentage', 5, 2);

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_components');
    }
};
