<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, decimal, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Inserir configurações padrão
        DB::table('system_settings')->insert([
            ['key' => 'gateway_pix_percentage', 'value' => '0', 'type' => 'decimal', 'description' => 'Taxa percentual PIX (%)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'gateway_pix_fixed', 'value' => '0.04', 'type' => 'decimal', 'description' => 'Taxa fixa PIX (R$)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'payment_method_pix', 'value' => 'true', 'type' => 'boolean', 'description' => 'Método de pagamento PIX ativo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'payment_method_credit_card', 'value' => 'true', 'type' => 'boolean', 'description' => 'Método de pagamento Cartão de Crédito ativo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'payment_method_boleto', 'value' => 'true', 'type' => 'boolean', 'description' => 'Método de pagamento Boleto ativo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
