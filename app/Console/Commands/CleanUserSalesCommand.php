<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\LiberpaySale;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUserSalesCommand extends Command
{
    protected $signature = 'sales:clean-user {email : Email do usuário}';
    protected $description = 'Remove todas as vendas e transações de um usuário específico';

    public function handle(): int
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuário com email {$email} não encontrado!");
            return 1;
        }

        $this->info("Limpando vendas do usuário: {$user->name} ({$user->email})");

        // Contar dados a serem deletados
        $transactionsCount = Transaction::where('user_id', $user->id)
            ->where('is_sample', false)
            ->count();
        
        $salesCount = LiberpaySale::where('user_id', $user->id)
            ->count();

        if ($transactionsCount === 0 && $salesCount === 0) {
            $this->info('Nenhuma venda encontrada para este usuário.');
            return 0;
        }

        if (!$this->confirm("Deseja deletar {$transactionsCount} transação(ões) e {$salesCount} venda(s)?", true)) {
            $this->info('Operação cancelada.');
            return 0;
        }

        // Desabilitar temporariamente foreign key checks para SQLite
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        try {
            // Deletar transações
            $deletedTransactions = Transaction::where('user_id', $user->id)
                ->where('is_sample', false)
                ->delete();

            // Deletar vendas Liberpay
            $deletedSales = LiberpaySale::where('user_id', $user->id)
                ->delete();

            // Zerar saldo do usuário
            $user->update(['balance' => 0]);

        } finally {
            // Reabilitar foreign key checks
            if (config('database.default') === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        $this->info("✓ {$deletedTransactions} transação(ões) deletada(s)");
        $this->info("✓ {$deletedSales} venda(s) deletada(s)");
        $this->info("✓ Saldo do usuário zerado");
        $this->newLine();
        $this->info("Vendas do usuário removidas com sucesso!");

        return 0;
    }
}

