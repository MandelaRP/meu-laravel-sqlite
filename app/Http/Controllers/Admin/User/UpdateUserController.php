<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SetLogTrait;
use Illuminate\Http\Request;

class UpdateUserController extends Controller
{
    use SetLogTrait;

    /**
     * Atualiza o status do usuário
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, User $user)
    {
        try {
            $validate = $request->validate([
                'status' => 'sometimes|string|in:active,inactive,pending,blocked,recent_user',
                'acquirer_id' => 'sometimes|nullable|exists:acquirers,id',
                'preferred_acquirer' => 'sometimes|nullable|string|in:liberpay,fullpix',
                'cash_in_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
                'cash_in_fixed' => 'sometimes|nullable|numeric|min:0',
                'cash_out_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
                'cash_out_fixed' => 'sometimes|nullable|numeric|min:0',
            ]);

            // Atualizar apenas os campos fornecidos (não altera taxas globais)
            $updateData = [];
            
            if (isset($validate['status'])) {
                $updateData['status'] = $validate['status'];
            }
            if (isset($validate['acquirer_id'])) {
                $updateData['acquirer_id'] = $validate['acquirer_id'];
            }
            if (isset($validate['preferred_acquirer'])) {
                $updateData['preferred_acquirer'] = $validate['preferred_acquirer'];
            }
            
            // Taxas personalizadas (null = usar global, valor = usar personalizada)
            if (array_key_exists('cash_in_percentage', $validate)) {
                $updateData['cash_in_percentage'] = $validate['cash_in_percentage'];
            }
            if (array_key_exists('cash_in_fixed', $validate)) {
                $updateData['cash_in_fixed'] = $validate['cash_in_fixed'];
            }
            if (array_key_exists('cash_out_percentage', $validate)) {
                $updateData['cash_out_percentage'] = $validate['cash_out_percentage'];
            }
            if (array_key_exists('cash_out_fixed', $validate)) {
                $updateData['cash_out_fixed'] = $validate['cash_out_fixed'];
            }

            $user->update($updateData);

            $this->setLog(
                channel: 'user',
                message: "Taxas do usuário {$user->email} atualizadas",
                type: 'info'
            );

            return redirect()->route('users.show', $user)->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao atualizar usuário: ' . $th->getMessage(), type: 'error');

            return redirect()->back()->with('error', 'Erro ao atualizar usuário');
        }
    }
}
