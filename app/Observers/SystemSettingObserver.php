<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SystemSettingObserver
{
    /**
     * Handle the SystemSetting "updated" event.
     */
    public function updated(SystemSetting $systemSetting): void
    {
        // Verificar se foi atualizada alguma taxa global
        $taxKeys = [
            'gateway_pix_percentage',
            'gateway_pix_fixed',
            'percent_withdraw_fee',
            'fixed_withdraw_fee',
        ];

        if (!in_array($systemSetting->key, $taxKeys)) {
            return;
        }

        // Aguardar um pouco para garantir que a atualização foi commitada
        // e buscar as taxas globais atualizadas
        $this->updateUsersToMatchGlobalTaxes();
    }

    /**
     * Handle the SystemSetting "saved" event (também disparado em create/update).
     */
    public function saved(SystemSetting $systemSetting): void
    {
        // Verificar se foi atualizada alguma taxa global
        $taxKeys = [
            'gateway_pix_percentage',
            'gateway_pix_fixed',
            'percent_withdraw_fee',
            'fixed_withdraw_fee',
        ];

        if (!in_array($systemSetting->key, $taxKeys)) {
            return;
        }

        // Atualizar usuários que têm taxas personalizadas iguais à nova taxa global
        $this->updateUsersToMatchGlobalTaxes();
    }

    /**
     * Atualiza usuários que têm taxas personalizadas iguais às taxas globais
     */
    private function updateUsersToMatchGlobalTaxes(): void
    {
        // Buscar todas as taxas globais atuais
        $globalCashInPercentage = (float) (SystemSetting::where('key', 'gateway_pix_percentage')->first()?->value ?? 0);
        $globalCashInFixed = (float) (SystemSetting::where('key', 'gateway_pix_fixed')->first()?->value ?? 0);
        $globalCashOutPercentage = (float) (SystemSetting::where('key', 'percent_withdraw_fee')->first()?->value ?? 0);
        $globalCashOutFixed = (float) (SystemSetting::where('key', 'fixed_withdraw_fee')->first()?->value ?? 0);

        // Função auxiliar para comparar valores
        $compareValues = function ($val1, $val2) {
            return abs((floatval($val1) ?: 0) - (floatval($val2) ?: 0)) < 0.01;
        };

        // Atualizar usuários que têm taxas personalizadas iguais à nova taxa global
        $users = User::where(function ($query) {
            $query->whereNotNull('cash_in_percentage')
                ->orWhereNotNull('cash_in_fixed')
                ->orWhereNotNull('cash_out_percentage')
                ->orWhereNotNull('cash_out_fixed');
        })->get();

        $updatedCount = 0;

        foreach ($users as $user) {
            $updates = [];

            // Verificar Cash In Percentage
            if ($user->cash_in_percentage !== null) {
                $userVal = (float) $user->cash_in_percentage;
                if ($compareValues($userVal, $globalCashInPercentage)) {
                    $updates['cash_in_percentage'] = null;
                }
            }

            // Verificar Cash In Fixed
            if ($user->cash_in_fixed !== null) {
                $userVal = (float) $user->cash_in_fixed;
                if ($compareValues($userVal, $globalCashInFixed)) {
                    $updates['cash_in_fixed'] = null;
                }
            }

            // Verificar Cash Out Percentage
            if ($user->cash_out_percentage !== null) {
                $userVal = (float) $user->cash_out_percentage;
                if ($compareValues($userVal, $globalCashOutPercentage)) {
                    $updates['cash_out_percentage'] = null;
                }
            }

            // Verificar Cash Out Fixed
            if ($user->cash_out_fixed !== null) {
                $userVal = (float) $user->cash_out_fixed;
                if ($compareValues($userVal, $globalCashOutFixed)) {
                    $updates['cash_out_fixed'] = null;
                }
            }

            if (!empty($updates)) {
                $user->update($updates);
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            Log::info("Taxas globais atualizadas. {$updatedCount} usuários atualizados para usar taxas globais.");
        }
    }
}

