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
            $table->decimal('fixed_fee', 10, 2)->default(0.00)->after('gateway_fee_percentage')->comment('Taxa fixa em reais');
            $table->decimal('percentage_fee', 5, 2)->default(0.00)->after('fixed_fee')->comment('Taxa percentual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acquirers', function (Blueprint $table) {
            $table->dropColumn(['fixed_fee', 'percentage_fee']);
        });
    }
};
