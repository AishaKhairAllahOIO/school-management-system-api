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
        Schema::create('academic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('current_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();

            $table->string('passing_grade'); 
            $table->integer('maximum_grade'); 
            $table->enum('gpa_scale', ['4.0', '5.0', '100']);
            $table->integer('minimum_attendance_percentage');
            $table->integer('promotion_threshold');

            $table->boolean('auto_promote_students')->default(false);
            $table->boolean('allow_student_repeating')->default(false);
            $table->boolean('calculate_gpa')->default(true);
            $table->boolean('rank_students')->default(true);
            $table->boolean('use_attendance_in_promotion')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_settings');
    }
};
