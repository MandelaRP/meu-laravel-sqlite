<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Acquirers;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAcquirersController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        // Resetar status "checking" antigos (mais de 30 segundos) para "offline"
        // Isso evita que fique travado em "verificando"
        Acquirer::where('api_status', 'checking')
            ->where('updated_at', '<', now()->subSeconds(30))
            ->update(['api_status' => 'offline']);

        $acquirers = Acquirer::orderBy('name')->get()->map(function ($acquirer) {
            // Garantir que credentials seja sempre um array, mesmo se null
            if (!$acquirer->credentials) {
                $acquirer->credentials = [];
            }
            return $acquirer;
        });

        // Buscar adquirente ativa
        $activeAcquirer = Acquirer::where('is_active', true)->first();

        // Buscar taxas da LuckPay
        $gatewayPixFixed = \App\Models\SystemSetting::get('gateway_pix_fixed', 0.04);
        $gatewayPixPercentage = \App\Models\SystemSetting::get('gateway_pix_percentage', 0);

        return Inertia::render('Admin/Acquirers/Index', [
            'acquirers' => $acquirers,
            'activeAcquirer' => $activeAcquirer,
            'gatewayPixFixed' => $gatewayPixFixed,
            'gatewayPixPercentage' => $gatewayPixPercentage,
        ]);
    }
}
