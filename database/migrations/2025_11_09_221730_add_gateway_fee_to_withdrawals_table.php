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
        if (Schema::hasTable('withdrawals') && !Schema::hasColumn('withdrawals', 'gateway_fee')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->decimal('gateway_fee', 10, 2)->default(0)->after('fee')->comment('Taxa da gateway (lucro líquido da plataforma)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('withdrawals') && Schema::hasColumn('withdrawals', 'gateway_fee')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->dropColumn('gateway_fee');
            });
        }
    }
};
