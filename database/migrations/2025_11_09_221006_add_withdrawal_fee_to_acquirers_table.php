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
            $table->decimal('withdrawal_fee', 10, 2)->default(0.00)->after('percentage_fee')->comment('Taxa de saque da adquirente em reais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acquirers', function (Blueprint $table) {
            $table->dropColumn('withdrawal_fee');
        });
    }
};
