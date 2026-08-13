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
        Schema::create('staff_financial_contracts', function (Blueprint $table) {
          $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            
            $table->enum('salary_type', ['per_period', 'fixed_monthly']);
            $table->decimal('salary_amount', 10, 2); // سعر الحصة أو الراتب الشهري الثابت
            
            $table->timestamps();

            $table->unique(['staff_id', 'academic_year_id'], 'staff_year_contract_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_financial_contracts');
    }
};
