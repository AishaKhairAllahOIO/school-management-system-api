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
        Schema::create('staff_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('payment_type', 20); // 'paid', 'unpaid'
            $table->unsignedSmallInteger('max_days_per_academic_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_leave_types');
    }
};
