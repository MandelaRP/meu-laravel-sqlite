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
        // Adicionar is_sample em transactions
        if (!Schema::hasColumn('transactions', 'is_sample')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->boolean('is_sample')->default(false)->after('fee');
            });
        }

        // Adicionar is_sample em products
        if (!Schema::hasColumn('products', 'is_sample')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->boolean('is_sample')->default(false)->after('stock');
            });
        }

        // Adicionar is_sample em users
        if (!Schema::hasColumn('users', 'is_sample')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_sample')->default(false)->after('status');
            });
        }

        // Adicionar is_sample em withdrawals (se a tabela existir)
        if (Schema::hasTable('withdrawals') && !Schema::hasColumn('withdrawals', 'is_sample')) {
            Schema::table('withdrawals', function (Blueprint $table): void {
                $table->boolean('is_sample')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'is_sample')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->dropColumn('is_sample');
            });
        }

        if (Schema::hasColumn('products', 'is_sample')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('is_sample');
            });
        }

        if (Schema::hasColumn('users', 'is_sample')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('is_sample');
            });
        }

        if (Schema::hasTable('withdrawals') && Schema::hasColumn('withdrawals', 'is_sample')) {
            Schema::table('withdrawals', function (Blueprint $table): void {
                $table->dropColumn('is_sample');
            });
        }
    }
};
