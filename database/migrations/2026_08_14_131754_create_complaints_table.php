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
    Schema::create('complaints', function (Blueprint $table) {
        $table->id();
        
        $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
        $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
        $table->foreignId('complaint_type_id')->constrained('complaint_types')->cascadeOnDelete();
        
        $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
        $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
