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
        Schema::create('liberpay_sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('liberpay_sale_id')->unique(); // ID da venda na Liberpay
            $table->string('reference_code')->nullable(); // Código de referência
            $table->string('external_reference')->nullable(); // Referência externa
            $table->decimal('amount', 10, 2); // Valor da venda
            $table->string('currency', 3)->default('BRL'); // Moeda
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled', 'refunded'])->default('pending');
            $table->text('pix_qr_code')->nullable(); // Código PIX copia e cola
            $table->text('pix_qr_code_image')->nullable(); // Imagem do QR Code (base64)
            $table->timestamp('expires_at')->nullable(); // Data de expiração do QR Code
            $table->timestamp('paid_at')->nullable(); // Data do pagamento
            $table->json('metadata')->nullable(); // Metadados adicionais
            $table->json('liberpay_response')->nullable(); // Resposta completa da API
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('liberpay_sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liberpay_sales');
    }
};
