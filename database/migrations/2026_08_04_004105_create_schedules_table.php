<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function ($table) {

            $table->id();

            $table->foreignId('academic_year_id');

            $table->foreignId('academic_term_id');

            $table->string('status')->default('draft');

            $table->decimal('score', 8, 2)->nullable();

            $table->json('generation_statistics')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
