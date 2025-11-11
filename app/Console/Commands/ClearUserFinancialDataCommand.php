<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\LiberpaySale;
use App\Models\FullPixSale;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearUserFinancialDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:clear-user {email : Email do usuário}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera saldo, transações e saques de um usuário específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuário com email '{$email}' não encontrado!");
            return Command::FAILURE;
        }

        $this->info("Limpando dados financeiros do usuário: {$user->name} ({$user->email})");

        try {
            DB::beginTransaction();

            // Zerar saldo do usuário
            $user->update(['balance' => 0]);
            $this->info('✓ Saldo zerado');

            // Deletar todas as transações do usuário
            $transactionsCount = Transaction::where('user_id', $user->id)
                ->where('is_sample', false)
                ->count();
            Transaction::where('user_id', $user->id)
                ->where('is_sample', false)
                ->delete();
            $this->info("✓ {$transactionsCount} transação(ões) deletada(s)");

            // Deletar todas as vendas Liberpay do usuário
            $liberpaySalesCount = LiberpaySale::where('user_id', $user->id)->count();
            LiberpaySale::where('user_id', $user->id)->delete();
            $this->info("✓ {$liberpaySalesCount} venda(s) Liberpay deletada(s)");

            // Deletar todas as vendas FullPix do usuário
            if (Schema::hasTable('fullpix_sales')) {
                $fullpixSalesCount = FullPixSale::where('user_id', $user->id)->count();
                FullPixSale::where('user_id', $user->id)->delete();
                $this->info("✓ {$fullpixSalesCount} venda(s) FullPix deletada(s)");
            }

            // Deletar todos os saques do usuário
            if (Schema::hasTable('withdrawals')) {
                $withdrawalsCount = DB::table('withdrawals')
                    ->where('user_id', $user->id)
                    ->where('is_sample', false)
                    ->count();
                DB::table('withdrawals')
                    ->where('user_id', $user->id)
                    ->where('is_sample', false)
                    ->delete();
                $this->info("✓ {$withdrawalsCount} saque(s) deletado(s)");
            }

            // Zerar campos financeiros adicionais do usuário
            $user->update([
                'volume_transacionado' => 0,
                'approved_deposits' => 0,
                'approved_deposits_net' => 0,
                'profit_for_platform' => 0,
                'value_paid_in_taxes' => 0,
                'withdraw_amount' => 0,
                'deposit_amount' => 0,
                'average_monthly_income' => 0,
            ]);
            $this->info('✓ Campos financeiros zerados');

            DB::commit();

            $this->info('✅ Dados financeiros do usuário zerados com sucesso!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro ao limpar dados financeiros: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

