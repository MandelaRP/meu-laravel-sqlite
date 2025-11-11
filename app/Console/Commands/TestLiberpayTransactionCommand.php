<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestLiberpayTransactionCommand extends Command
{
    protected $signature = 'liberpay:test-transaction';
    protected $description = 'Testa diferentes formatos de autenticação para criar transação';

    public function handle(): int
    {
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if (!$acquirer || !$acquirer->credentials) {
            $this->error('Adquirente não configurada!');
            return 1;
        }

        $publicKey = $acquirer->credentials['chave_publica'] ?? '';
        $privateKey = $acquirer->credentials['chave_privada'] ?? '';
        $baseUrl = 'https://app.liberpay.pro/v1';

        $this->info('Testando diferentes formatos de autenticação para criar transação...');
        $this->newLine();

        $payload = [
            'amount' => 100,
            'paymentMethod' => 'pix',
            'items' => [
                [
                    'title' => 'Teste',
                    'quantity' => 1,
                    'unitPrice' => 100,
                    'tangible' => false,
                ]
            ],
            'customer' => [
                'name' => 'Teste',
                'email' => 'teste@teste.com',
                'phone' => '11999999999',
                'document' => [
                    'type' => 'cpf',
                    'number' => '12345678901',
                ],
            ],
            'pix' => [
                'expiresIn' => 30,
            ],
        ];

        // Teste 1: Basic Auth (chave pública:chave privada)
        $this->line('Teste 1: Basic Auth (public:private)');
        $credentials = base64_encode($publicKey . ':' . $privateKey);
        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $credentials,
            ])->post("{$baseUrl}/transactions", $payload);
        
        $this->line("  Status: {$response->status()}");
        $body = $response->body();
        if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
            $this->error('  ✗ Retornou HTML (página de login)');
        } else {
            $this->info('  ✓ Retornou JSON!');
            $this->line('  Resposta: ' . substr($body, 0, 200));
        }
        $this->newLine();

        // Teste 2: Basic Auth (chave privada:chave pública) - ordem invertida
        $this->line('Teste 2: Basic Auth (private:public) - ordem invertida');
        $credentials = base64_encode($privateKey . ':' . $publicKey);
        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $credentials,
            ])->post("{$baseUrl}/transactions", $payload);
        
        $this->line("  Status: {$response->status()}");
        $body = $response->body();
        if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
            $this->error('  ✗ Retornou HTML (página de login)');
        } else {
            $this->info('  ✓ Retornou JSON!');
            $this->line('  Resposta: ' . substr($body, 0, 200));
        }
        $this->newLine();

        // Teste 3: X-API-Key com chave privada
        $this->line('Teste 3: X-API-Key com chave privada');
        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $privateKey,
            ])->post("{$baseUrl}/transactions", $payload);
        
        $this->line("  Status: {$response->status()}");
        $body = $response->body();
        if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
            $this->error('  ✗ Retornou HTML (página de login)');
        } else {
            $this->info('  ✓ Retornou JSON!');
            $this->line('  Resposta: ' . substr($body, 0, 200));
        }
        $this->newLine();

        // Teste 4: Authorization Bearer com chave privada
        $this->line('Teste 4: Authorization Bearer com chave privada');
        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $privateKey,
            ])->post("{$baseUrl}/transactions", $payload);
        
        $this->line("  Status: {$response->status()}");
        $body = $response->body();
        if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
            $this->error('  ✗ Retornou HTML (página de login)');
        } else {
            $this->info('  ✓ Retornou JSON!');
            $this->line('  Resposta: ' . substr($body, 0, 200));
        }
        $this->newLine();

        return 0;
    }
}

