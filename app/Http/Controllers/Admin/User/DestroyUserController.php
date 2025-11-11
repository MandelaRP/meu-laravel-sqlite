<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SetLogTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyUserController extends Controller
{
    use SetLogTrait;

    /**
     * Exclui um usuário
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        try {
            // Salvar email antes de deletar para usar no log
            $userEmail = $user->email;
            $userId = $user->id;

            // Não permitir excluir o próprio usuário
            if ($userId === $request->user()->id) {
                return redirect()->back()->with('error', 'Você não pode excluir seu próprio usuário.');
            }

            // Permitir excluir qualquer usuário (incluindo admins) se tiver permissão de admin
            // Permitir excluir usuários com qualquer status (pending, active, recent_user, etc)
            // Forçar exclusão mesmo com relacionamentos
            try {
                // Tentar deletar normalmente primeiro
                $user->delete();
            } catch (\Exception $e) {
                // Se falhar, forçar exclusão direta no banco
                \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->delete();
            }

            $this->setLog(channel: 'user', message: "Usuário {$userEmail} (ID: {$userId}) excluído com sucesso", type: 'info');

            return redirect()->route('users.index')
                ->with('success', 'Usuário excluído com sucesso!');
        } catch (\Throwable $th) {
            $this->setLog(channel: 'user', message: 'Erro ao excluir usuário: ' . $th->getMessage(), type: 'error');

            return redirect()->back()->with('error', 'Erro ao excluir usuário. A exclusão foi cancelada.');
        }
    }
}

