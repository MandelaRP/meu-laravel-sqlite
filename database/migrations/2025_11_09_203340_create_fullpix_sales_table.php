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
        Schema::create('fullpix_sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('fullpix_transaction_id')->unique(); // ID da transação na FullPix
            $table->string('reference_code')->nullable(); // Código de referência
            $table->string('external_reference')->nullable(); // Referência externa
            $table->decimal('amount', 10, 2); // Valor da transação
            $table->string('currency', 3)->default('BRL'); // Moeda
            $table->enum('status', ['pending', 'waiting_payment', 'paid', 'refused', 'cancelled', 'refunded', 'expired'])->default('waiting_payment');
            $table->text('pix_qrcode')->nullable(); // Código PIX copia e cola
            $table->text('pix_qrcode_image')->nullable(); // Imagem do QR Code (base64)
            $table->timestamp('expires_at')->nullable(); // Data de expiração do QR Code
            $table->timestamp('paid_at')->nullable(); // Data do pagamento
            $table->json('metadata')->nullable(); // Metadados adicionais
            $table->json('fullpix_response')->nullable(); // Resposta completa da API
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('fullpix_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fullpix_sales');
    }
};
