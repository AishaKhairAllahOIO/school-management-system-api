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
        Schema::create('installment_policy_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_policy_id')->constrained('installment_policies')->cascadeOnDelete();
            
            $table->tinyInteger('installment_number')->unsigned(); // 1, 2, 3..
            $table->string('title', 100); // "الدفعة التأسيسية"
            $table->decimal('percentage', 5, 2); // النسبة المئوية: 50.00
            
            // يوم وشهر الاستحقاق الافتراضي (لحساب التاريخ الحقيقي لاحقاً)
            $table->tinyInteger('due_month')->unsigned(); // 1-12
            $table->tinyInteger('due_day')->unsigned();   // 1-31
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_policy_items');
    }
};
