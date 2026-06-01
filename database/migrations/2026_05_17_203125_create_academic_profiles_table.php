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
        Schema::create('academic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
           //$table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('degree', ['diploma', 'bachelor', 'master', 'phd', 'other']);
            $table->string('specialization', 100); 
            $table->string('university'); 
            $table->unsignedSmallInteger('graduation_year');
            $table->unsignedTinyInteger('experience_years')->default(0);          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_profiles');
    }
};
