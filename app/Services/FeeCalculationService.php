<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Acquirer;
use App\Models\SystemSetting;
use App\Models\User;

class FeeCalculationService
{
    /**
     * Calcula o valor líquido após todas as taxas
     * 
     * @param float $totalAmount Valor total da transação
     * @param User|null $user Usuário (opcional, para taxas individuais e adquirente preferida)
     * @param Acquirer|null $acquirer Adquirente específica (opcional, se não fornecida, usa preferência do cliente ou global)
     * @return array ['net_amount' => float, 'acquirer_fee' => float, 'gateway_fee' => float, 'total_fees' => float]
     */
    public function calculateNetAmount(float $totalAmount, ?User $user = null, ?Acquirer $acquirer = null): array
    {
        // Usar BC Math para cálculos precisos com decimais
        // Converter para string para manter precisão (2 casas decimais)
        $totalAmountStr = number_format($totalAmount, 2, '.', '');
        
        // Determinar qual adquirente usar (prioridade: parâmetro > preferência do cliente > configuração global)
        $activeAcquirer = $acquirer;
        
        if (!$activeAcquirer && $user && $user->preferred_acquirer) {
            // Se o cliente tem adquirente preferida, usar ela
            $activeAcquirer = Acquirer::where('slug', $user->preferred_acquirer)->first();
        }
        
        if (!$activeAcquirer) {
            // Usar configuração global (adquirente ativa)
            $activeAcquirer = Acquirer::where('is_active', true)->first();
        }
        
        // Taxa da adquirente (fixa + percentual)
        $acquirerFixedFee = 0.00;
        $acquirerPercentageFee = 0.00;
        
        if ($activeAcquirer) {
            $acquirerFixedFee = (float) ($activeAcquirer->fixed_fee ?? 0.00);
            $acquirerPercentageFee = (float) ($activeAcquirer->percentage_fee ?? 0.00);
        }
        
        // Taxas da gateway (LuckPay)
        $gatewayPercentage = $this->getGatewayPercentage($user);
        $gatewayFixed = $this->getGatewayFixed($user);
        
        // Lógica: Se taxa LuckPay > 0, ela já engloba a taxa da adquirente
        // Se taxa LuckPay = 0, aplicar apenas taxa da adquirente
        if ($gatewayFixed > 0 || $gatewayPercentage > 0) {
            // Taxa total = taxa fixa LuckPay + (valor * taxa percentual LuckPay / 100)
            $gatewayPercentageStr = number_format($gatewayPercentage, 2, '.', '');
            $percentageFee = bcmul($totalAmountStr, bcdiv($gatewayPercentageStr, '100', 4), 2);
            $gatewayFixedStr = number_format($gatewayFixed, 2, '.', '');
            $totalFees = (float) bcadd($percentageFee, $gatewayFixedStr, 2);
            
            // Lucro LuckPay = taxa total - taxa fixa adquirente
            $acquirerFixedFeeStr = number_format($acquirerFixedFee, 2, '.', '');
            $gatewayFee = (float) bcsub((string) $totalFees, $acquirerFixedFeeStr, 2);
            $gatewayFee = max(0, $gatewayFee); // Garantir que não seja negativo
            
            // Taxa da adquirente (apenas para referência, já incluída na taxa total)
            $acquirerFee = $acquirerFixedFee;
        } else {
            // Taxa total = taxa fixa adquirente + (valor * taxa percentual adquirente / 100)
            $acquirerPercentageFeeStr = number_format($acquirerPercentageFee, 2, '.', '');
            $acquirerPercentageAmount = bcmul($totalAmountStr, bcdiv($acquirerPercentageFeeStr, '100', 4), 2);
            $acquirerFixedFeeStr = number_format($acquirerFixedFee, 2, '.', '');
            $totalFees = (float) bcadd($acquirerPercentageAmount, $acquirerFixedFeeStr, 2);
            
            // Lucro LuckPay = 0
            $gatewayFee = 0.00;
            
            // Taxa total da adquirente
            $acquirerFee = (float) $totalFees;
        }
        
        // Valor líquido (valor bruto - taxa total)
        $netAmount = (float) bcsub($totalAmountStr, (string) $totalFees, 2);
        
        return [
            'net_amount' => max(0, $netAmount), // Garantir que não seja negativo
            'acquirer_fee' => $acquirerFee,
            'gateway_fee' => $gatewayFee,
            'total_fees' => $totalFees,
        ];
    }
    
    /**
     * Obtém a taxa percentual da gateway (LuckPay)
     */
    private function getGatewayPercentage(?User $user = null): float
    {
        // Se o usuário tem taxa individual configurada, usar ela
        if ($user && $user->cash_in_percentage !== null) {
            return (float) $user->cash_in_percentage;
        }
        
        // Caso contrário, usar a taxa do sistema (sem valor padrão, usar 0 se não configurado)
        $setting = SystemSetting::where('key', 'gateway_pix_percentage')->first();
        return $setting ? (float) $setting->value : 0.00;
    }
    
    /**
     * Obtém a taxa fixa da gateway (LuckPay)
     */
    private function getGatewayFixed(?User $user = null): float
    {
        // Se o usuário tem taxa individual configurada, usar ela
        if ($user && $user->cash_in_fixed !== null) {
            return (float) $user->cash_in_fixed;
        }
        
        // Caso contrário, usar a taxa do sistema (sem valor padrão, usar 0 se não configurado)
        $setting = SystemSetting::where('key', 'gateway_pix_fixed')->first();
        return $setting ? (float) $setting->value : 0.00;
    }
    
    /**
     * Calcula taxas de saque
     */
    public function calculateWithdrawalFees(float $amount, ?User $user = null): array
    {
        // Taxa da LiberPay para saque (R$ 5,00)
        $liberpayWithdrawalFee = 5.00;
        
        // Taxas da gateway (LuckPay) para saque
        $gatewayPercentage = $this->getWithdrawalPercentage($user);
        $gatewayFixed = $this->getWithdrawalFixed($user);
        
        $gatewayFee = ($amount * $gatewayPercentage / 100) + $gatewayFixed;
        
        // Total de taxas de saque
        $totalFees = $liberpayWithdrawalFee + $gatewayFee;
        
        // Valor líquido após saque
        $netAmount = $amount - $totalFees;
        
        return [
            'net_amount' => max(0, $netAmount),
            'liberpay_fee' => $liberpayWithdrawalFee,
            'gateway_fee' => $gatewayFee,
            'total_fees' => $totalFees,
        ];
    }
    
    /**
     * Obtém a taxa percentual de saque da gateway
     */
    private function getWithdrawalPercentage(?User $user = null): float
    {
        if ($user && $user->cash_out_percentage !== null) {
            return (float) $user->cash_out_percentage;
        }
        
        // Taxa padrão de saque (pode ser configurada no futuro)
        return 0;
    }
    
    /**
     * Obtém a taxa fixa de saque da gateway
     */
    private function getWithdrawalFixed(?User $user = null): float
    {
        if ($user && $user->cash_out_fixed !== null) {
            return (float) $user->cash_out_fixed;
        }
        
        // Taxa padrão de saque da LuckPay (R$ 1,00)
        return 1.00;
    }
    
    /**
     * Retorna a taxa fixa da adquirente ativa
     */
    public static function getActiveAcquirerFixedFee(): float
    {
        $activeAcquirer = Acquirer::where('is_active', true)->first();
        return $activeAcquirer ? (float) ($activeAcquirer->fixed_fee ?? 0.00) : 0.00;
    }
}

