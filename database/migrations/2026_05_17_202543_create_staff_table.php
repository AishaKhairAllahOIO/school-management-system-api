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
        Schema::create('staff', function (Blueprint $table) {
    $table->id();
    $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
    
    // الحقول المشتركة لجميع البشر في النظام
    $table->string('first_name');
    $table->string('last_name');
    $table->string('father_name');
    $table->string('mother_name');
    $table->date('birth_date');
    $table->string('birth_place');
    $table->enum('gender', ['male', 'female']);
    $table->enum('nationality', ['syrian', 'lebanese', 'palestinian', 'jordanian', 'other'])->default('syrian');
    $table->string('phone_number', 15);
    $table->string('address')->nullable();
    $table->string('personal_photo')->nullable();
    $table->date('hire_date'); 
    $table->string('employee_type')->nullable();
    $table->enum('record_status', ['active', 'draft', 'archived', 'deleted'])->default('active');
    $table->timestamps();
    $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
