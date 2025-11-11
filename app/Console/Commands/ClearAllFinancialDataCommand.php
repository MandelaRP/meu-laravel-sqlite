<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\LiberpaySale;
use App\Models\FullPixSale;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearAllFinancialDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:clear-all {--force : Forçar limpeza sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera todos os saldos, transações, saques e métricas financeiras de toda a gateway';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Tem certeza que deseja zerar TODOS os dados financeiros da gateway? Esta ação não pode ser desfeita!')) {
                $this->info('Operação cancelada.');
                return Command::SUCCESS;
            }
        }

        $this->info('Zerando todos os dados financeiros da gateway...');

        try {
            DB::beginTransaction();

            // Zerar saldos de todos os usuários
            $usersCount = User::count();
            User::query()->update(['balance' => 0]);
            $this->info("✓ Saldos de {$usersCount} usuário(s) zerados");

            // Zerar campos financeiros adicionais de todos os usuários
            User::query()->update([
                'volume_transacionado' => 0,
                'approved_deposits' => 0,
                'approved_deposits_net' => 0,
                'profit_for_platform' => 0,
                'value_paid_in_taxes' => 0,
                'withdraw_amount' => 0,
                'deposit_amount' => 0,
                'average_monthly_income' => 0,
            ]);
            $this->info('✓ Campos financeiros de todos os usuários zerados');

            // Deletar todas as transações (exceto is_sample)
            $transactionsCount = Transaction::where('is_sample', false)->count();
            Transaction::where('is_sample', false)->delete();
            $this->info("✓ {$transactionsCount} transação(ões) deletada(s)");

            // Deletar todas as vendas Liberpay
            $liberpaySalesCount = LiberpaySale::count();
            LiberpaySale::query()->delete();
            $this->info("✓ {$liberpaySalesCount} venda(s) Liberpay deletada(s)");

            // Deletar todas as vendas FullPix
            if (Schema::hasTable('fullpix_sales')) {
                $fullpixSalesCount = FullPixSale::count();
                FullPixSale::query()->delete();
                $this->info("✓ {$fullpixSalesCount} venda(s) FullPix deletada(s)");
            }

            // Deletar todos os saques (exceto is_sample)
            if (Schema::hasTable('withdrawals')) {
                $withdrawalsCount = DB::table('withdrawals')
                    ->where('is_sample', false)
                    ->count();
                DB::table('withdrawals')
                    ->where('is_sample', false)
                    ->delete();
                $this->info("✓ {$withdrawalsCount} saque(s) deletado(s)");
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Todos os dados financeiros da gateway foram zerados com sucesso!');
            $this->info('A estrutura da gateway permanece intacta. Apenas os dados financeiros foram limpos.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro ao limpar dados financeiros: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}

