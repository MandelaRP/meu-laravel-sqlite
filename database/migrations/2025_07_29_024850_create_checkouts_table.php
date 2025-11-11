<?php

declare(strict_types = 1);

use App\Enums\Seller\CheckoutLayoutEnum;
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
        Schema::create('checkouts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // Relações
            $table->foreignId('user_id')->constrained('users');
            $table->foreignUuid('product_id')->constrained('products');
            $table->unsignedInteger('discount_percentage')->default(0);

            // Configurações básicas
            $table->enum('layout', CheckoutLayoutEnum::values())->default(CheckoutLayoutEnum::SINGLE->value);
            $table->string('banner')->nullable();

            // Contagem regressiva
            $table->boolean('countdown_enabled')->default(true);
            $table->string('countdown_icon', 50)->nullable();
            $table->unsignedInteger('countdown_duration')->nullable(); // até 65535 segundos (~24h)
            $table->char('countdown_bg_color', 7)->default('#dc2626');
            $table->char('countdown_text_color', 7)->default('#ffffff');
            $table->string('countdown_message', 255)->nullable();
            $table->boolean('countdown_expired')->default(false);

            // Botões principais
            $table->char('button_primary_color', 7)->default('#10b981');
            $table->char('button_secondary_color', 7)->default('#059669');
            $table->char('button_hover_primary_color', 7)->default('#059669');
            $table->char('button_hover_secondary_color', 7)->default('#047857');

            // Configurações do formulário
            $table->json('form_fields_config')->nullable()->comment('Configuração dos campos do formulário de checkout');
            $table->json('form_requirements')->nullable()->comment('Campos obrigatórios do formulário');

            // Cores principais do layout
            $table->char('background_color', 7)->default('#f8fafc')->comment('Cor de fundo do checkout');
            $table->char('text_color', 7)->default('#0f172a')->comment('Cor do texto do checkout');

            // Cores dos steps
            $table->boolean('stepped_form_enabled')->default(false)->comment('Habilitar formulário em etapas');
            $table->json('steps')->nullable()->comment('Configuração dos steps do checkout');

            // Métodos de pagamento habilitados
            $table->json('payment_methods')->nullable()->comment('PIX, boleto, cartão etc.');

            // Order Bump Customization
            $table->char('order_bump_bg_color', 7)->default('#ffffff')->comment('Cor de fundo do order bump');
            $table->char('order_bump_text_color', 7)->default('#0f172a')->comment('Cor do texto do order bump');
            $table->char('order_bump_border_color', 7)->default('#fbbf24')->comment('Cor da borda do order bump');
            $table->text('order_bump_description')->nullable()->comment('Descrição do order bump');
            $table->string('order_bump_cta_text', 255)->default('Quero comprar também!')->comment('Texto do CTA do order bump');
            $table->char('order_bump_cta_bg_color', 7)->default('#10b981')->comment('Cor de fundo do CTA do order bump');
            $table->char('order_bump_cta_text_color', 7)->default('#ffffff')->comment('Cor do texto do CTA do order bump');
            $table->string('order_bump_recommended_text', 255)->default('(Recomendado)')->comment('Texto de recomendação do order bump');
            $table->char('order_bump_recommended_color', 7)->default('#fbbf24')->comment('Cor do texto de recomendação do order bump');
            $table->boolean('order_bump_enabled')->default(true)->comment('Habilitar order bump');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
