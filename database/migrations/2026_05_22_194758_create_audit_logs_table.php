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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('model_type'); // App\Models\Business
            $table->unsignedBigInteger('model_id');
            $table->string('action'); // created, updated, deleted
            $table->json('old_values')->nullable(); // valores anteriores
            $table->json('new_values')->nullable(); // valores nuevos
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id']);
            $table->index(['action']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
