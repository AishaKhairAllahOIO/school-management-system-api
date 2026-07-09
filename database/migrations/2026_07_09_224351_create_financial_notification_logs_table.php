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
        Schema::create('financial_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->foreignId('scheduled_installment_id')->nullable()->constrained('scheduled_installments')->nullOnDelete();
            
            $table->enum('notification_type', ['upcoming_reminder', 'overdue_alert', 'payment_received']);
            $table->text('message_content');
            $table->timestamp('sent_at'); // وقت الإرسال الفعلي
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_notification_logs');
    }
};
