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
        Schema::create('teacher_workloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('staff')->cascadeOnDelete();
            $table->integer('required_monthly_periods');
            $table->integer('assigned_monthly_periods')->default(0);
            $table->integer('remaining_monthly_periods')->virtualAs('required_monthly_periods - assigned_monthly_periods');
            $table->timestamps();
            $table->unique(['academic_year_id', 'teacher_id'], 'unique_workload_per_year');
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_workloads');
    }
};

