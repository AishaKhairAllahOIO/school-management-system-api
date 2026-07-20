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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('degree', ['diploma', 'bachelor', 'master', 'phd', 'other'])->nullable();
            $table->string('specialization', 100)->nullable();
            $table->string('university')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->date('hire_date');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->string('service_type', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
