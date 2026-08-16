<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counselor_availabilities', function (Blueprint $table) {

            $table->id();

            $table->foreignId('counselor_id')
                ->constrained('staff')
                ->cascadeOnDelete();


            $table->string('day');


            $table->time('start_time');

            $table->time('end_time');


            $table->unsignedInteger('session_duration');


            $table->unsignedInteger('daily_sessions_limit');


            $table->boolean('is_active')
                ->default(true);


            $table->timestamps();


            $table->unique([
                'counselor_id',
                'day'
            ]);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('counselor_availabilities');
    }
};