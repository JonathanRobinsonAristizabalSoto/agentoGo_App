<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('primary_color', 32)->nullable();
            $table->string('secondary_color', 32)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
