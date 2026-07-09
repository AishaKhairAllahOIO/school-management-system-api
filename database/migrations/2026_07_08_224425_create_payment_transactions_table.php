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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            
            $table->decimal('paid_amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'electronic_wallet']);
            
            $table->string('paper_receipt_no', 50)->nullable(); // إجباري إذا كان الدفع كاش
            $table->string('digital_reference', 100)->nullable(); // إجباري للإلكتروني
            
            // من هو المستخدم (أمين السر/المحاسب) الذي استلم الدفعة؟
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
