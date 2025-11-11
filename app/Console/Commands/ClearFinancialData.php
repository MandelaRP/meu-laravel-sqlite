<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\LiberpaySale;
use App\Models\User;
use Illuminate\Console\Command;

class ClearFinancialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera todos os saldos e transações do sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Zerando dados financeiros...');

        // Zerar saldos de todos os usuários
        User::query()->update(['balance' => 0]);
        $this->info('✓ Saldos zerados');

        // Deletar todas as transações (exceto is_sample se necessário)
        $transactionsCount = Transaction::where('is_sample', false)->count();
        Transaction::where('is_sample', false)->delete();
        $this->info("✓ {$transactionsCount} transações deletadas");

        // Deletar todas as vendas do Liberpay
        $salesCount = LiberpaySale::count();
        LiberpaySale::query()->delete();
        $this->info("✓ {$salesCount} vendas Liberpay deletadas");

        $this->info('✅ Dados financeiros zerados com sucesso!');
        
        return Command::SUCCESS;
    }
}
