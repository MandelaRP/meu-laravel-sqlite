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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('cash_in_percentage', 5, 2)->nullable()->after('acquirer_id');
            $table->decimal('cash_in_fixed', 10, 2)->nullable()->after('cash_in_percentage');
            $table->decimal('cash_out_percentage', 5, 2)->nullable()->after('cash_in_fixed');
            $table->decimal('cash_out_fixed', 10, 2)->nullable()->after('cash_out_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cash_in_percentage', 'cash_in_fixed', 'cash_out_percentage', 'cash_out_fixed']);
        });
    }
};
