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
        if (!Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('fullpix_withdrawal_id')->nullable()->unique()->comment('ID do saque na FullPix');
                $table->enum('pix_type', ['CPF', 'CNPJ', 'EMAIL', 'PHONE', 'EVP'])->default('CPF');
                $table->string('pix_key')->comment('Chave PIX de destino');
                $table->decimal('amount', 10, 2)->comment('Valor solicitado');
                $table->decimal('fee', 10, 2)->default(0)->comment('Taxa cobrada');
                $table->decimal('net_amount', 10, 2)->comment('Valor líquido após taxas');
                $table->enum('status', ['pending', 'approved', 'processing', 'done', 'done_manual', 'failed', 'refused', 'cancelled'])->default('pending');
                $table->text('description')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->json('fullpix_response')->nullable();
                $table->boolean('is_sample')->default(false);
                $table->timestamps();

                $table->index('user_id');
                $table->index('status');
                $table->index('fullpix_withdrawal_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
