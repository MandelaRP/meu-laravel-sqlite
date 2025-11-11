<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Withdrawals;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelWithdrawalController extends Controller
{
    /**
     * Cancelar saque
     */
    public function __invoke(Request $request, $id): RedirectResponse
    {
        try {
            $hasWithdrawalsTable = DB::getSchemaBuilder()->hasTable('withdrawals');
            
            if (!$hasWithdrawalsTable) {
                return redirect()->route('admin.withdrawals.index')
                    ->with('error', 'Tabela de saques não encontrada.');
            }

            $withdrawal = DB::table('withdrawals')
                ->where('id', $id)
                ->where('is_sample', false)
                ->first();

            if (!$withdrawal) {
                return redirect()->route('admin.withdrawals.index')
                    ->with('error', 'Saque não encontrado.');
            }

            if ($withdrawal->status === 'cancelled') {
                return redirect()->route('admin.withdrawals.index')
                    ->with('error', 'Este saque já foi cancelado.');
            }

            if ($withdrawal->status === 'approved' || $withdrawal->status === 'done') {
                return redirect()->route('admin.withdrawals.index')
                    ->with('error', 'Não é possível cancelar um saque já aprovado.');
            }

            DB::beginTransaction();

            try {
                $user = User::find($withdrawal->user_id);
                if ($user) {
                    // VERIFICAÇÃO CRÍTICA: Verificar se há outros saques processados (done, approved, processing)
                    // criados após este saque que está sendo cancelado
                    // Se houver, NÃO devolver o saldo para evitar duplicação
                    $hasProcessedWithdrawals = DB::table('withdrawals')
                        ->where('user_id', $withdrawal->user_id)
                        ->where('id', '!=', $id)
                        ->whereIn('status', ['done', 'approved', 'processing', 'done_manual'])
                        ->where('created_at', '>', $withdrawal->created_at)
                        ->exists();
                    
                    if ($hasProcessedWithdrawals) {
                        // Há outros saques processados após este, não devolver saldo
                        Log::warning('Saque cancelado mas saldo NÃO restaurado - há outros saques processados', [
                            'withdrawal_id' => $id,
                            'user_id' => $withdrawal->user_id,
                            'amount_not_restored' => $withdrawal->amount,
                            'status_anterior' => $withdrawal->status,
                            'reason' => 'Existem outros saques processados criados após este saque',
                        ]);
                    } else {
                        // Não há outros saques processados, pode restaurar o saldo com segurança
                        $user->increment('balance', $withdrawal->amount);
                        
                        Log::info('Saldo restaurado ao cancelar saque', [
                            'withdrawal_id' => $id,
                            'user_id' => $withdrawal->user_id,
                            'amount_restored' => $withdrawal->amount,
                            'status_anterior' => $withdrawal->status,
                        ]);
                    }
                }

                // Atualizar status para cancelado
                DB::table('withdrawals')
                    ->where('id', $id)
                    ->update([
                        'status' => 'cancelled',
                        'updated_at' => now()
                    ]);

                DB::commit();

                Log::info('Saque cancelado com sucesso', [
                    'withdrawal_id' => $id,
                    'user_id' => $withdrawal->user_id,
                    'status_anterior' => $withdrawal->status,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return redirect()->route('admin.withdrawals.index')
                ->with('cancelled', 'Saque cancelado com sucesso!');
        } catch (\Throwable $th) {
            Log::error('Erro ao cancelar saque', [
                'withdrawal_id' => $id,
                'error' => $th->getMessage(),
            ]);

            return redirect()->route('admin.withdrawals.index')
                ->with('error', 'Erro ao cancelar saque: ' . $th->getMessage());
        }
    }
}
