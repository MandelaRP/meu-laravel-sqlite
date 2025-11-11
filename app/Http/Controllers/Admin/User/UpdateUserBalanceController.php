<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateUserBalanceController extends Controller
{
    use SetLogTrait;

    /**
     * Atualiza o saldo do usuário
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|string|in:add,remove',
            ]);

            $amount = (float) $validated['amount'];
            $type = $validated['type'];
            $oldBalance = $user->balance ?? 0;

            if ($type === 'add') {
                $user->increment('balance', $amount);
                $newBalance = $oldBalance + $amount;
                $this->setLog(
                    channel: 'user',
                    message: "Saldo adicionado ao usuário {$user->email}: R$ " . number_format($amount, 2, ',', '.') . " (Saldo anterior: R$ " . number_format($oldBalance, 2, ',', '.') . " → Novo saldo: R$ " . number_format($newBalance, 2, ',', '.') . ")",
                    type: 'info'
                );
            } else {
                $newBalance = max(0, $oldBalance - $amount);
                $user->update(['balance' => $newBalance]);
                $this->setLog(
                    channel: 'user',
                    message: "Saldo removido do usuário {$user->email}: R$ " . number_format($amount, 2, ',', '.') . " (Saldo anterior: R$ " . number_format($oldBalance, 2, ',', '.') . " → Novo saldo: R$ " . number_format($newBalance, 2, ',', '.') . ")",
                    type: 'info'
                );
            }

            return redirect()->route('users.show', $user)
                ->with('success', 'Saldo atualizado com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao atualizar saldo: ' . $th->getMessage(), type: 'error');

            return redirect()->back()
                ->with('error', 'Erro ao atualizar saldo: ' . $th->getMessage());
        }
    }
}

