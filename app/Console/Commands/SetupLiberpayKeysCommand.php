<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use Illuminate\Console\Command;

class SetupLiberpayKeysCommand extends Command
{
    protected $signature = 'liberpay:setup-keys {--public=} {--private=} {--withdrawal=}';

    protected $description = 'Configura as chaves da Liberpay no banco de dados';

    public function handle(): int
    {
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if (!$acquirer) {
            $this->error('Adquirente Liberpay não encontrada. Execute: php artisan db:seed --class=AcquirerSeeder');
            return 1;
        }

        $publicKey = $this->option('public') ?: 'pk_b1oml4xC0wS9WB8BinrnxrFX_mr5ZuV0xn9-5GmupZUdDN5P';
        $privateKey = $this->option('private') ?: 'sk_V3x0bNVpnraBlY_kJxa9E9-pAVDgGCcXuiOSFnzn3K9L9cZi';
        $withdrawalKey = $this->option('withdrawal') ?: 'wk_qmaqxwFI3RgDKqI77B27XQPUZYamjg4lxeSpb74LMoP-OgaL';

        $acquirer->credentials = [
            'chave_publica' => $publicKey,
            'chave_privada' => $privateKey,
            'chave_saque_externo' => $withdrawalKey,
        ];
        
        $acquirer->is_active = true;
        $acquirer->api_status = 'checking';
        $acquirer->gateway_fee_percentage = 2.99;
        
        $acquirer->save();

        $this->info('✓ Chaves configuradas e adquirente ativada!');
        $this->line('Chave Pública: ' . substr($publicKey, 0, 20) . '...');
        $this->line('Chave Privada: ' . substr($privateKey, 0, 20) . '...');
        $this->line('Chave Saque: ' . substr($withdrawalKey, 0, 20) . '...');
        
        return 0;
    }
}

