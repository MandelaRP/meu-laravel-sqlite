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
        Schema::create('financial_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('cash_in_percentage', 5, 2)->default(5.00);
            $table->decimal('cash_in_fixed_value', 10, 2)->default(2.50);
            $table->decimal('cash_out_percentage', 5, 2)->default(3.50);
            $table->decimal('cash_out_fixed_value', 10, 2)->default(1.80);
            $table->decimal('minimum_cash_in_value', 10, 2)->default(10.00);
            $table->decimal('maximum_cash_in_value', 10, 2)->default(50.00);
            $table->decimal('minimum_cash_out_value', 10, 2)->default(5.00);
            $table->decimal('maximum_cash_out_value', 10, 2)->default(25.00);
            $table->timestamps();

            // Garante apenas uma configuração por usuário
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_settings');
    }
};
