<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Acquirers;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use App\Services\LiberpayService;
use App\Services\FullPixService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckApiStatusController extends Controller
{
    public function __construct(
        private readonly LiberpayService $liberpayService,
        private readonly FullPixService $fullPixService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $acquirer = Acquirer::findOrFail($id);
        
        // SEMPRE verificar a API quando solicitado, não fazer verificação prévia
        // Atualiza status para "checking" temporariamente
        $acquirer->api_status = 'checking';
        $acquirer->save();

        try {
            $apiStatus = 'offline';

            // Verificar status baseado no slug da adquirente
            if ($acquirer->slug === 'liberpay') {
                $apiStatus = $this->liberpayService->checkApiStatus();
            } elseif ($acquirer->slug === 'fullpix') {
                $apiStatus = $this->fullPixService->checkApiStatus();
            } else {
                // Para outras adquirentes, implementar lógica específica
                $apiStatus = 'offline';
            }

            // Recarregar a adquirente para pegar o estado atual de is_active
            $acquirer->refresh();
            
            // Atualizar status - se não estiver ativa, mostrar como desativada
            // Mas manter o status real da API para informação
            if (!$acquirer->is_active) {
                // Se desativada, mostrar como offline, mas manter o status real da API
                // para que o usuário saiba se a API está funcionando
            }
            
            $acquirer->api_status = $apiStatus;
            $acquirer->save();
            
            // Recarregar para garantir dados atualizados
            $acquirer->refresh();

            // Mapear status para mensagem amigável
            $statusText = match($apiStatus) {
                'online' => 'Ativo',
                'offline' => 'Desativada',
                'error' => 'Erro',
                default => 'Desconhecido'
            };

            return redirect()->route('admin.acquirers.index')
                ->with('success', sprintf(
                    'Status da API %s: %s',
                    $acquirer->name,
                    $statusText
                ))
                ->with('acquirers', Acquirer::orderBy('name')->get());
        } catch (\Exception $e) {
            // Garantir que não fique em "checking" em caso de erro
            $acquirer->refresh();
            $acquirer->api_status = 'error';
            $acquirer->save();
            $acquirer->refresh();

            return redirect()->route('admin.acquirers.index')
                ->with('error', 'Erro ao verificar status da API: ' . $e->getMessage())
                ->with('acquirers', Acquirer::orderBy('name')->get());
        }
    }
}
