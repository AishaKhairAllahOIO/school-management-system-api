<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('material_user_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_material_id')->constrained('study_materials')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['study_material_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_user_reads');
    }
};
