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
            $table->integer('required_monthly_periods');
            $table->integer('assigned_monthly_periods');
            $table->foreignId('teacher_assignment_id')->constrained('teacher_assignments')->cascadeOnDelete();
            $table->timestamps();
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
