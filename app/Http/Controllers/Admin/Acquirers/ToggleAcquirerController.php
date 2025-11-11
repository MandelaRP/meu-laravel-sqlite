<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Acquirers;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use App\Services\LiberpayService;
use App\Services\FullPixService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ToggleAcquirerController extends Controller
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
        
        // Receber o novo estado do request (PUT)
        $isActive = $request->input('is_active', false);
        $wasActive = $acquirer->is_active;
        
        // Se estiver ativando, desativar todas as outras adquirentes primeiro
        if ($isActive && !$wasActive) {
            // Desativar todas as outras adquirentes
            Acquirer::where('id', '!=', $id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }
        
        $acquirer->is_active = (bool) $isActive;
        
        // Se ativar e não estava ativa antes, verificar status da API automaticamente
        if ($acquirer->is_active && !$wasActive) {
            // Verificar status da API automaticamente
            if ($acquirer->slug === 'liberpay') {
                try {
                    $apiStatus = $this->liberpayService->checkApiStatus();
                    $acquirer->api_status = $apiStatus;
                } catch (\Exception $e) {
                    // Se falhar, manter status atual
                    $acquirer->api_status = 'offline';
                }
            } elseif ($acquirer->slug === 'fullpix') {
                try {
                    $apiStatus = $this->fullPixService->checkApiStatus();
                    $acquirer->api_status = $apiStatus;
                } catch (\Exception $e) {
                    // Se falhar, manter status atual
                    $acquirer->api_status = 'offline';
                }
            }
        }
        
        $acquirer->save();
        
        // Recarregar a adquirente para garantir dados atualizados
        $acquirer->refresh();

        $message = sprintf(
            'Adquirente %s com sucesso.',
            $acquirer->is_active ? 'ativada' : 'desativada'
        );
        
        if ($acquirer->is_active && $acquirer->api_status === 'online') {
            $message .= ' API está online.';
        }

        return redirect()->route('admin.acquirers.index')
            ->with('success', $message)
            ->with('acquirers', Acquirer::orderBy('name')->get());
    }
}
