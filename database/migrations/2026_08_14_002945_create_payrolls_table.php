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
        Schema::create('payrolls', function (Blueprint $table) {
       $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained('staff_financial_contracts')->restrictOnDelete();
            
            $table->unsignedSmallInteger('year');  // مثال: 2026
            $table->unsignedTinyInteger('month');  // مثال: 10 (لشهر أكتوبر)
            
            $table->date('payment_date'); 
            $table->decimal('net_salary', 10, 2); 
            
            $table->timestamps();
        

            $table->unique(['staff_id', 'year', 'month'], 'staff_monthly_payroll_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
