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
        Schema::create('scheduled_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            
            $table->tinyInteger('installment_number')->unsigned();
            $table->string('title', 100);
            $table->decimal('amount_due', 12, 2); // المطلوب في هذا القسط
            $table->decimal('amount_paid', 12, 2)->default(0.00); // ما تم سداده
            
            $table->date('due_date'); // التاريخ الميلادي الحقيقي المستنتج
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_installments');
    }
};
