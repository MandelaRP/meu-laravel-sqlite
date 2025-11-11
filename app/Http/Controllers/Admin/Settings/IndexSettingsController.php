<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexSettingsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        // Buscar taxas sem valor padrão para permitir 0
        $gatewayPixPercentageSetting = SystemSetting::where('key', 'gateway_pix_percentage')->first();
        $gatewayPixFixedSetting = SystemSetting::where('key', 'gateway_pix_fixed')->first();
        
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'gateway_pix_percentage' => $gatewayPixPercentageSetting ? (float) $gatewayPixPercentageSetting->value : 0,
                'gateway_pix_fixed' => $gatewayPixFixedSetting ? (float) $gatewayPixFixedSetting->value : 0,
                'payment_method_pix' => SystemSetting::get('payment_method_pix', true),
                'payment_method_credit_card' => SystemSetting::get('payment_method_credit_card', true),
                'payment_method_boleto' => SystemSetting::get('payment_method_boleto', true),
                'min_withdraw' => SystemSetting::get('min_withdraw', 10.00),
                'fixed_withdraw_fee' => SystemSetting::get('fixed_withdraw_fee', 5.00),
                'percent_withdraw_fee' => SystemSetting::get('percent_withdraw_fee', 0),
                'transfer_mode' => SystemSetting::get('transfer_mode', 'manual'),
            ]
        ]);
    }
}

