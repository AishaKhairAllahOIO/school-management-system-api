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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->morphs('notifiable');

            $table->string('type')->index();
            $table->string('audience')->index();    
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['notifiable_id', 'notifiable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
