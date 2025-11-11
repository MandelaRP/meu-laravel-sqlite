<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acquirers', function (Blueprint $table): void {
            $table->id();
            $table->string('name'); // Nome da adquirente (ex: Liberpay)
            $table->string('slug')->unique(); // Identificador único (ex: liberpay)
            $table->string('description')->nullable(); // Descrição da adquirente
            $table->boolean('is_active')->default(false); // Se está ativa
            $table->enum('api_status', ['online', 'offline', 'error', 'checking'])->default('offline'); // Status da API
            $table->json('credentials')->nullable(); // Tokens, chaves, etc (JSON)
            $table->json('settings')->nullable(); // Configurações específicas (JSON)
            $table->string('logo_url')->nullable(); // URL do logo da adquirente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquirers');
    }
};
