<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\LiberpaySale;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanFakeDataCommand extends Command
{
    protected $signature = 'data:clean-fake {--keep-user= : Email do usuário a manter os dados}';
    protected $description = 'Remove todos os dados fictícios (transações e vendas)';

    public function handle(): int
    {
        $keepUserEmail = $this->option('keep-user');
        
        if ($keepUserEmail) {
            $keepUser = User::where('email', $keepUserEmail)->first();
            if (!$keepUser) {
                $this->error("Usuário com email {$keepUserEmail} não encontrado!");
                return 1;
            }
            $this->info("Mantendo dados do usuário: {$keepUser->name} ({$keepUser->email})");
        } else {
            $keepUser = null;
            $this->info("Removendo TODOS os dados fictícios...");
        }

        // Contar dados a serem deletados
        $transactionsCount = Transaction::where('is_sample', false)
            ->when($keepUser, fn($q) => $q->where('user_id', '!=', $keepUser->id))
            ->count();
        
        $salesCount = LiberpaySale::when($keepUser, fn($q) => $q->where('user_id', '!=', $keepUser->id))
            ->count();

        if ($transactionsCount === 0 && $salesCount === 0) {
            $this->info('Nenhum dado fictício para deletar.');
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
            $deletedTransactions = Transaction::where('is_sample', false)
                ->when($keepUser, fn($q) => $q->where('user_id', '!=', $keepUser->id))
                ->delete();

            // Deletar vendas Liberpay
            $deletedSales = LiberpaySale::when($keepUser, fn($q) => $q->where('user_id', '!=', $keepUser->id))
                ->delete();

            // Zerar saldo dos usuários (exceto o mantido)
            if ($keepUser) {
                User::where('id', '!=', $keepUser->id)
                    ->where('is_sample', false)
                    ->update(['balance' => 0]);
            } else {
                User::where('is_sample', false)
                    ->update(['balance' => 0]);
            }

        } finally {
            // Reabilitar foreign key checks
            if (config('database.default') === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        $this->info("✓ {$deletedTransactions} transação(ões) deletada(s)");
        $this->info("✓ {$deletedSales} venda(s) deletada(s)");
        $this->info("✓ Saldos zerados (exceto usuário mantido)");
        $this->newLine();
        $this->info("Dados fictícios removidos com sucesso!");

        return 0;
    }
}

