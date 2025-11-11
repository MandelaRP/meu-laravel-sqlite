<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SetLogTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserActionsController extends Controller
{
    use SetLogTrait;

    /**
     * Setar usuário como admin
     */
    public function setAdmin(Request $request, User $user): JsonResponse
    {
        try {
            $user->update(['role' => 'admin']);
            
            $this->setLog(channel: 'user', message: "Usuário {$user->email} definido como admin", type: 'info');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário definido como admin com sucesso!',
            ]);
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao definir admin: ' . $th->getMessage(), type: 'error');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao definir usuário como admin',
            ], 500);
        }
    }

    /**
     * Banir usuário
     */
    public function ban(Request $request, User $user): JsonResponse
    {
        try {
            $user->update(['status' => 'banned']);
            
            $this->setLog(channel: 'user', message: "Usuário {$user->email} banido", type: 'info');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário banido com sucesso!',
            ]);
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao banir usuário: ' . $th->getMessage(), type: 'error');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao banir usuário',
            ], 500);
        }
    }

    /**
     * Ativar usuário
     */
    public function activate(Request $request, User $user): JsonResponse
    {
        try {
            $wasPending = $user->status === 'pending';
            
            // Garantir que usuário aprovado use taxas globais (remover taxas personalizadas se existirem)
            $user->update([
                'status' => 'active',
                'cash_in_percentage' => null,
                'cash_in_fixed' => null,
                'cash_out_percentage' => null,
                'cash_out_fixed' => null,
            ]);
            
            // Se estava pendente e foi aprovado agora, marcar como recém aprovado
            if ($wasPending) {
                // Usar uma flag temporária que será verificada no frontend
                // O frontend verificará se o status mudou de pending para active
            }
            
            $this->setLog(channel: 'user', message: "Usuário {$user->email} ativado com taxas globais", type: 'info');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário ativado com sucesso!',
                'was_pending' => $wasPending,
            ]);
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao ativar usuário: ' . $th->getMessage(), type: 'error');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ativar usuário',
            ], 500);
        }
    }

    /**
     * Rejeitar usuário
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        try {
            $user->update(['status' => 'banned']);
            
            $this->setLog(channel: 'user', message: "Usuário {$user->email} rejeitado", type: 'info');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário rejeitado com sucesso!',
            ]);
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao rejeitar usuário: ' . $th->getMessage(), type: 'error');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao rejeitar usuário',
            ], 500);
        }
    }
}

