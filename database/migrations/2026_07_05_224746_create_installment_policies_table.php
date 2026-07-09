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
        Schema::create('installment_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // مثلاً: "تقسيط على دفعتين"
            $table->tinyInteger('installments_count')->unsigned(); // عدد الدفعات
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_policies');
    }
};
