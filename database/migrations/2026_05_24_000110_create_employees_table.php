<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('position', 120)->nullable();
            $table->date('hire_date')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['business_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};