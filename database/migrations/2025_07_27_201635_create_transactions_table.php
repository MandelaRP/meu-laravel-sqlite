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
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice')->unique()->comment('Código único da transação');
            $table->enum('payment_status', ['Paid', 'Pending', 'Unpaid'])->default('Pending');
            $table->decimal('total_amount', 10, 2)->comment('Valor bruto da transação');
            $table->string('payment_method')->comment('Método de pagamento');
            $table->decimal('net_deposit', 10, 2)->comment('Valor líquido depositado');
            $table->string('acquirer_ref')->nullable()->comment('Referência do adquirente');
            $table->date('date')->comment('Data da transação');
            $table->decimal('fee', 10, 2)->comment('Taxa da transação');
            $table->timestamps();

            // Index para consultas frequentes
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
