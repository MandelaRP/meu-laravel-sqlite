<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestLiberpayApiCommand extends Command
{
    protected $signature = 'liberpay:test-api 
                            {--url= : URL base da API para testar}
                            {--public-key= : Chave pública}
                            {--private-key= : Chave privada}';

    protected $description = 'Testa diferentes URLs da API Liberpay para encontrar a correta';

    public function handle(): int
    {
        $publicKey = $this->option('public-key') ?: 'pk_b1oml4xC0wS9WB8BinrnxrFX_mr5ZuV0xn9-5GmupZUdDN5P';
        $privateKey = $this->option('private-key') ?: 'sk_V3x0bNVpnraBlY_kJxa9E9-pAVDgGCcXuiOSFnzn3K9L9cZi';

        $urlsToTest = [
            'https://app.liberpay.pro/api/v1',
            'https://app.liberpay.pro/v1',
            'https://api.liberpay.pro/v1',
            'https://api.liberpay.pro/api/v1',
            'https://liberpay.pro/api/v1',
            'https://liberpay.pro/v1',
        ];

        if ($this->option('url')) {
            $urlsToTest = [$this->option('url')];
        }

        $this->info('Testando URLs da API Liberpay...');
        $this->newLine();

        $found = false;

        foreach ($urlsToTest as $baseUrl) {
            $this->info("Testando: {$baseUrl}");
            
            try {
                // Teste 1: Balance endpoint
                $balanceUrl = "{$baseUrl}/balance";
                $this->line("  → GET {$balanceUrl}");
                
                $response = Http::timeout(10)
                    ->withOptions(['verify' => false]) // Desabilitar SSL para teste
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-Key' => $publicKey,
                    ])
                    ->get($balanceUrl);

                $status = $response->status();
                $body = $response->body();
                
                if ($response->successful()) {
                    $this->info("  ✓ SUCESSO! Status: {$status}");
                    $json = $response->json();
                    $this->line("  Resposta: " . json_encode($json, JSON_PRETTY_PRINT));
                    $this->newLine();
                    $this->info("URL CORRETA ENCONTRADA: {$baseUrl}");
                    $found = true;
                    break;
                } else {
                    $this->warn("  ✗ Erro: Status {$status}");
                    if ($status === 404) {
                        $this->line("  → Endpoint não encontrado");
                    } elseif (in_array($status, [401, 403])) {
                        $this->line("  → Erro de autenticação (pode ser que a URL esteja correta mas as chaves estejam erradas)");
                        $this->line("  → Resposta: " . substr($body, 0, 200));
                    } else {
                        $this->line("  → Resposta: " . substr($body, 0, 200));
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Exceção: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        if (!$found) {
            $this->error('Nenhuma URL funcionou. Verifique:');
            $this->line('1. As chaves estão corretas?');
            $this->line('2. A documentação oficial mostra qual é a URL base?');
            $this->line('3. Há algum problema de rede/firewall?');
            return 1;
        }

        return 0;
    }
}

