<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Withdrawals;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveWithdrawalController extends Controller
{
    /**
     * Aprovar saque
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

            if (in_array($withdrawal->status, ['approved', 'done', 'done_manual'])) {
                return redirect()->route('admin.withdrawals.index')
                    ->with('error', 'Este saque já foi finalizado.');
            }

            if ($withdrawal->status === 'cancelled') {
                return redirect()->route('admin.withdrawals.index')
                    ->with('error', 'Não é possível aprovar um saque cancelado.');
            }

            DB::beginTransaction();

            // Atualizar status para "done" (Finalizado) para manter consistência
            DB::table('withdrawals')
                ->where('id', $id)
                ->update([
                    'status' => 'done',
                    'paid_at' => now(),
                    'updated_at' => now()
                ]);

            // O saldo já foi descontado ao criar o saque (tanto modo manual quanto automático)
            // Portanto, não é necessário descontar novamente ao aprovar

            DB::commit();

            Log::info('Saque aprovado manualmente', [
                'withdrawal_id' => $id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
            ]);

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Saque finalizado com sucesso!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao aprovar saque', [
                'withdrawal_id' => $id,
                'error' => $th->getMessage(),
            ]);

            return redirect()->route('admin.withdrawals.index')
                ->with('error', 'Erro ao aprovar saque: ' . $th->getMessage());
        }
    }
}
