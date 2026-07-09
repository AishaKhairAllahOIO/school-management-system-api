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
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->unsignedBigInteger('fee_plan_id')->nullable()->change();
            $table->unsignedBigInteger('installment_policy_id')->nullable()->change();
            $table->decimal('total_required_amount', 12, 2)->nullable()->change();
            $table->decimal('remaining_balance', 12, 2)->nullable()->change() ;   // الرصيد الحي (يبدأ بقيمة الفاتورة ويتناقص)
            $table->enum('payment_status', ['draft','unpaid', 'partially_paid', 'fully_paid'])->default('draft');
            
            $table->unique(['student_id', 'academic_year_id'], 'unique_financial_account_per_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
