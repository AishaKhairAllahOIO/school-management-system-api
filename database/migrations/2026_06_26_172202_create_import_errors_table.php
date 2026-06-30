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
        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('row_data');
            $table->string('error_message');
            $table->index(['import_batch_id', 'row_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_errors');
    }
};
