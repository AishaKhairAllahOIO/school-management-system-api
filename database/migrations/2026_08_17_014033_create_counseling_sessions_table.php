<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('appointment_id')
                ->unique()
                ->constrained('counselor_appointments')
                ->cascadeOnDelete();

            $table->enum('attendance_status', [
                'not_marked',
                'present',
                'absent',
            ])->default('not_marked');

            $table->enum('assessment', [
                'normal',
                'follow_up',
                'critical',
            ])->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};