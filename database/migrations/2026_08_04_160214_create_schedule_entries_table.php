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
        Schema::create('schedule_entries', function ($table) {

            $table->id();

            $table->foreignId('schedule_id');

            $table->unsignedBigInteger('teacher_assignment_id');

            $table->unsignedBigInteger('teacher_id');

            $table->unsignedBigInteger('class_room_id');

            $table->unsignedBigInteger('grade_subject_id');

            $table->string('day');

            $table->integer('period_index');

            $table->boolean('is_locked')->default(false);

            $table->string('source')->default('generated');

            $table->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_entries');
    }
};
