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
            $table->foreignId('current_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('current_semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->json('schedule_settings');
            
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
