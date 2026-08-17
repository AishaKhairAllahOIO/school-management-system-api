<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counselor_appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('counselor_id')
                ->constrained('staff')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete();

            $table->date('appointment_date');

            $table->time('start_time');
            $table->time('end_time');

            $table->enum('booking_status', [
                'available',
                'pending',
                'accepted',
                'not_available',
                'cancelled',
                'completed',
            ])->default('available');

            $table->timestamps();

            $table->unique(
                [
                    'counselor_id',
                    'appointment_date',
                    'start_time'
                ],
                'counselor_slot_unique'
            );

           $table->index([
            'counselor_id',
            'appointment_date',
            'booking_status',
        ], 'counselor_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselor_appointments');
    }
};
