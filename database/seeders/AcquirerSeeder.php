<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Acquirer;
use Illuminate\Database\Seeder;

class AcquirerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Adicionar Liberpay (não sobrescrever chaves existentes)
        $acquirer = Acquirer::firstOrNew(['slug' => 'liberpay']);
        
        // Só atualizar campos se não existirem
        if (!$acquirer->exists) {
            $acquirer->name = 'Liberpay';
            $acquirer->description = 'Adquirente de pagamento Liberpay';
            $acquirer->is_active = true; // Pré-configurar como ativa
            $acquirer->api_status = 'offline';
            $acquirer->gateway_fee_percentage = 2.99;
            $acquirer->fixed_fee = 1.00; // Taxa fixa R$ 1,00
            $acquirer->percentage_fee = 0.00; // Taxa percentual 0%
            $acquirer->credentials = [
                'chave_publica' => '',
                'chave_privada' => '',
                'chave_saque_externo' => '',
            ];
            $acquirer->settings = [];
            $acquirer->logo_url = null;
        } else {
            // Se já existe, apenas atualizar campos que não afetam as chaves
            $acquirer->name = 'Liberpay';
            $acquirer->description = 'Adquirente de pagamento Liberpay';
            // Manter gateway_fee_percentage se não existir
            if (!$acquirer->gateway_fee_percentage) {
                $acquirer->gateway_fee_percentage = 2.99;
            }
            // Configurar taxas se não existirem
            if ($acquirer->fixed_fee === null || $acquirer->fixed_fee == 0) {
                $acquirer->fixed_fee = 1.00;
            }
            if ($acquirer->percentage_fee === null) {
                $acquirer->percentage_fee = 0.00;
            }
        }
        
        $acquirer->save();
        
        // Adicionar FullPix
        $fullpixAcquirer = Acquirer::firstOrNew(['slug' => 'fullpix']);
        
        if (!$fullpixAcquirer->exists) {
            $fullpixAcquirer->name = 'FullPix';
            $fullpixAcquirer->description = 'Adquirente de pagamento FullPix';
            $fullpixAcquirer->is_active = false; // Inativa por padrão
            $fullpixAcquirer->api_status = 'offline';
            $fullpixAcquirer->gateway_fee_percentage = 0;
            $fullpixAcquirer->fixed_fee = 0.50; // Taxa fixa R$ 0,50
            $fullpixAcquirer->percentage_fee = 0.00; // Taxa percentual 0%
            $fullpixAcquirer->withdrawal_fee = 0.50; // Taxa de saque R$ 0,50
            $fullpixAcquirer->credentials = [
                'secret_key' => '',
                'public_key' => '',
            ];
            $fullpixAcquirer->settings = [];
            $fullpixAcquirer->logo_url = null;
        } else {
            $fullpixAcquirer->name = 'FullPix';
            $fullpixAcquirer->description = 'Adquirente de pagamento FullPix';
            // Configurar taxas se não existirem
            if ($fullpixAcquirer->fixed_fee === null || $fullpixAcquirer->fixed_fee == 0) {
                $fullpixAcquirer->fixed_fee = 0.50;
            }
            if ($fullpixAcquirer->percentage_fee === null) {
                $fullpixAcquirer->percentage_fee = 0.00;
            }
            if ($fullpixAcquirer->withdrawal_fee === null || $fullpixAcquirer->withdrawal_fee == 0) {
                $fullpixAcquirer->withdrawal_fee = 0.50;
            }
        }
        
        $fullpixAcquirer->save();
        
        // Garantir que apenas uma adquirente esteja ativa
        // Por padrão, LiberPay fica ativa
        Acquirer::where('slug', '!=', 'liberpay')
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
