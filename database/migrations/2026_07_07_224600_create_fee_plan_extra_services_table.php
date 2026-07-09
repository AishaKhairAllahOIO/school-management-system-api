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
        Schema::create('fee_plan_extra_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_plan_id')->constrained('fee_plans')->cascadeOnDelete();
            
            $table->enum('type', ['uniform', 'books', 'activities', 'insurance', 'other']);
            $table->string('name', 100);
            $table->decimal('amount', 12, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_plan_extra_services');
    }
};
