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
        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained('grade_levels')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

            $table->integer('weekly_periods');
            $table->enum('difficulty', ['light', 'medium', 'heavy']);

            $table->decimal('max_mark', 8, 2);
            $table->decimal('passing_mark', 8, 2);
            $table->boolean('is_failing_subject')->default(true);
            $table->decimal('weight_in_total', 5, 2)->default(1.00);

            $table->integer('max_periods_per_day')->default(1);
            $table->boolean('avoid_first_period')->default(false);
            $table->boolean('avoid_last_period')->default(false);
            $table->json('preferred_period_indexes')->nullable();


            $table->timestamps();

            $table->unique(['semester_id', 'grade_level_id', 'subject_id'], 'grade_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
    }
};
