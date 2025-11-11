<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateSettingsController extends Controller
{
    /**
     * Atualiza as configurações do sistema
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gatewayFeePercentage' => 'nullable|numeric|min:0|max:100',
            'gatewayPixPercentage' => 'nullable|numeric|min:0|max:100',
            'gatewayPixFixed' => 'nullable|numeric|min:0',
            'payment_method_pix' => 'nullable|boolean',
            'payment_method_credit_card' => 'nullable|boolean',
            'payment_method_boleto' => 'nullable|boolean',
            'pixFee' => 'nullable|numeric|min:0|max:100',
            'minWithdraw' => 'nullable|numeric|min:0',
            'fixedWithdrawFee' => 'nullable|numeric|min:0',
            'percentWithdrawFee' => 'nullable|numeric|min:0|max:100',
            'reserveEnabled' => 'nullable|boolean',
            'reservePercent' => 'nullable|numeric|min:0|max:100',
            'reserveMax' => 'nullable|numeric|min:0',
            'transferMode' => 'nullable|string',
            'methods' => 'nullable|array',
        ]);

        try {
            // Atualizar gateway_fee_percentage na tabela acquirers
            if (isset($validated['gatewayFeePercentage'])) {
                $acquirer = Acquirer::where('slug', 'liberpay')->first();
                if ($acquirer) {
                    $acquirer->gateway_fee_percentage = (float) $validated['gatewayFeePercentage'];
                    $acquirer->save();
                }
            }

            // Salvar taxas PIX e Gateway (usar array_key_exists para permitir 0)
            if (array_key_exists('gatewayPixPercentage', $validated)) {
                SystemSetting::set('gateway_pix_percentage', (float) $validated['gatewayPixPercentage'], 'decimal', 'Taxa percentual PIX (%)');
            }
            
            if (array_key_exists('gatewayPixFixed', $validated)) {
                SystemSetting::set('gateway_pix_fixed', (float) $validated['gatewayPixFixed'], 'decimal', 'Taxa fixa PIX (R$)');
            }
            
            // Salvar métodos de pagamento
            if (isset($validated['payment_method_pix'])) {
                SystemSetting::set('payment_method_pix', (bool) $validated['payment_method_pix'], 'boolean', 'Método de pagamento PIX ativo');
            }
            
            if (isset($validated['payment_method_credit_card'])) {
                SystemSetting::set('payment_method_credit_card', (bool) $validated['payment_method_credit_card'], 'boolean', 'Método de pagamento Cartão de Crédito ativo');
            }
            
            if (isset($validated['payment_method_boleto'])) {
                SystemSetting::set('payment_method_boleto', (bool) $validated['payment_method_boleto'], 'boolean', 'Método de pagamento Boleto ativo');
            }
            
            // Salvar configurações de saque usando SystemSetting
            if (isset($validated['minWithdraw'])) {
                SystemSetting::set('min_withdraw', (float) $validated['minWithdraw'], 'decimal', 'Valor mínimo para saque');
            }
            
            if (isset($validated['fixedWithdrawFee'])) {
                SystemSetting::set('fixed_withdraw_fee', (float) $validated['fixedWithdrawFee'], 'decimal', 'Taxa fixa de saque (R$)');
            }
            
            if (isset($validated['percentWithdrawFee'])) {
                SystemSetting::set('percent_withdraw_fee', (float) $validated['percentWithdrawFee'], 'decimal', 'Taxa percentual de saque (%)');
            }
            
            // Salvar modo de transferência
            if (isset($validated['transferMode'])) {
                SystemSetting::set('transfer_mode', $validated['transferMode'], 'string', 'Modo de transferência (manual/automatico)');
            }
            
            // Salvar outras configurações (compatibilidade com código antigo)
            if (DB::getSchemaBuilder()->hasTable('settings')) {
                $settingsToSave = [
                    'pix_fee' => $validated['pixFee'] ?? null,
                    'reserve_enabled' => $validated['reserveEnabled'] ?? null,
                    'reserve_percent' => $validated['reservePercent'] ?? null,
                    'reserve_max' => $validated['reserveMax'] ?? null,
                ];

                foreach ($settingsToSave as $key => $value) {
                    if ($value !== null) {
                        DB::table('settings')->updateOrInsert(
                            ['key' => $key],
                            ['value' => is_array($value) ? json_encode($value) : $value, 'updated_at' => now()]
                        );
                    }
                }
            }

            Log::info('Configurações do sistema atualizadas', ['settings' => $validated]);

            return redirect()->back()->with('success', 'Configurações salvas com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar configurações', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['message' => 'Erro ao salvar configurações: ' . $e->getMessage()])
                ->withInput();
        }
    }
}

