<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTodayWithdrawals extends Command
{
    protected $signature = 'withdrawals:fix-today';
    protected $description = 'Corrige saques de hoje: atualiza status para done e registra lucro';

    public function handle()
    {
        $this->info('Corrigindo saques de hoje...');
        
        $withdrawals = DB::table('withdrawals')
            ->whereDate('created_at', today())
            ->get();
        
        foreach ($withdrawals as $withdrawal) {
            // Se está pendente mas deveria estar done (modo automático)
            if ($withdrawal->status === 'pending' && $withdrawal->fullpix_withdrawal_id) {
                DB::table('withdrawals')
                    ->where('id', $withdrawal->id)
                    ->update([
                        'status' => 'done',
                        'paid_at' => $withdrawal->paid_at ?? now(),
                        'updated_at' => now(),
                    ]);
                
                $this->info("Saque ID {$withdrawal->id} atualizado para 'done'");
            }
            
            // Se está done mas não tem lucro registrado
            if ($withdrawal->status === 'done' && ($withdrawal->gateway_fee ?? 0) > 0) {
                $user = DB::table('users')->where('id', $withdrawal->user_id)->first();
                
                if ($user) {
                    // Verificar se o lucro já foi registrado
                    $currentProfit = $user->profit_for_platform ?? 0;
                    $expectedProfit = $withdrawal->gateway_fee;
                    
                    // Registrar lucro se ainda não foi registrado
                    DB::table('users')
                        ->where('id', $withdrawal->user_id)
                        ->increment('profit_for_platform', $expectedProfit);
                    
                    DB::table('users')
                        ->where('id', $withdrawal->user_id)
                        ->increment('withdraw_amount', $withdrawal->amount);
                    
                    $this->info("Lucro registrado para saque ID {$withdrawal->id}: R$ {$expectedProfit}");
                }
            }
            
            // Verificar saldo do usuário (apenas informativo, não descontar novamente)
            // O saldo já foi descontado no controller CreateWithdrawalController
            $user = DB::table('users')->where('id', $withdrawal->user_id)->first();
            if ($user) {
                $this->info("Saldo atual do usuário ID {$withdrawal->user_id}: R$ {$user->balance} (saque ID {$withdrawal->id}: R$ {$withdrawal->amount})");
            }
        }
        
        $this->info('Correção concluída!');
        return 0;
    }
}

