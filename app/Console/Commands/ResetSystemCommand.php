<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the entire system, keeping only admin user and settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Tem certeza que deseja resetar o sistema? Todos os dados serão perdidos exceto o usuário admin.')) {
                $this->info('Operação cancelada.');
                return Command::SUCCESS;
            }
        }

        try {
            DB::beginTransaction();

            // Obter o ID do admin
            $admin = User::where('role', 'admin')->first();

            if (!$admin) {
                $this->error('Usuário admin não encontrado!');
                return Command::FAILURE;
            }

            $adminId = $admin->id;
            $adminEmail = $admin->email;
            $adminName = $admin->name;

            // Deletar todas as transações
            Transaction::query()->delete();
            $this->info('✓ Transações deletadas');

            // Deletar todos os usuários exceto admin
            User::where('id', '!=', $adminId)->delete();
            $this->info('✓ Usuários deletados (exceto admin)');

            // Resetar saldos do admin
            User::where('id', $adminId)->update([
                'balance' => 0,
                'volume_transacionado' => 0,
                'approved_deposits' => 0,
                'approved_deposits_net' => 0,
                'profit_for_platform' => 0,
                'value_paid_in_taxes' => 0,
                'withdraw_amount' => 0,
                'deposit_amount' => 0,
                'average_monthly_income' => 0,
            ]);
            $this->info('✓ Saldos resetados');

            DB::commit();

            $this->info("Sistema resetado com sucesso!");
            $this->info("Admin mantido: {$adminName} ({$adminEmail})");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro ao resetar sistema: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

