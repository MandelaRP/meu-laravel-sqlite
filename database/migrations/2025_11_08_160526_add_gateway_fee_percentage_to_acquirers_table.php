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
        Schema::table('acquirers', function (Blueprint $table) {
            $table->decimal('gateway_fee_percentage', 5, 2)->default(2.99)->after('api_status')
                ->comment('Porcentagem de comissão da gateway sobre o valor pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acquirers', function (Blueprint $table) {
            $table->dropColumn('gateway_fee_percentage');
        });
    }
};
