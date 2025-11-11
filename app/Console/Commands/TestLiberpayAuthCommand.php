<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestLiberpayAuthCommand extends Command
{
    protected $signature = 'liberpay:test-auth';

    protected $description = 'Testa diferentes formatos de autenticação da API Liberpay';

    public function handle(): int
    {
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if (!$acquirer) {
            $this->error('Adquirente não encontrada');
            return 1;
        }

        $publicKey = $acquirer->credentials['chave_publica'] ?? null;
        $privateKey = $acquirer->credentials['chave_privada'] ?? null;

        if (!$publicKey || !$privateKey) {
            $this->error('Chaves não configuradas');
            return 1;
        }

        $baseUrl = 'https://app.liberpay.pro/v1';
        $endpoint = "{$baseUrl}/sales";
        $amount = 10.00;

        $this->info('Testando diferentes formatos de autenticação...');
        $this->newLine();

        // Teste 1: X-API-Key com chave pública (atual)
        $this->line('Teste 1: X-API-Key com chave pública');
        $response1 = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $publicKey,
            ])
            ->post($endpoint, [
                'amount' => $amount,
                'currency' => 'BRL',
                'description' => 'Teste de autenticação',
                'payment_method' => 'pix',
            ]);
        
        $this->checkResponse($response1, 'X-API-Key (pública)');
        $this->newLine();

        // Teste 2: Authorization Bearer com chave pública
        $this->line('Teste 2: Authorization Bearer com chave pública');
        $response2 = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $publicKey,
            ])
            ->post($endpoint, [
                'amount' => $amount,
                'currency' => 'BRL',
                'description' => 'Teste de autenticação',
                'payment_method' => 'pix',
            ]);
        
        $this->checkResponse($response2, 'Authorization Bearer (pública)');
        $this->newLine();

        // Teste 3: X-API-Key com chave privada
        $this->line('Teste 3: X-API-Key com chave privada');
        $response3 = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $privateKey,
            ])
            ->post($endpoint, [
                'amount' => $amount,
                'currency' => 'BRL',
                'description' => 'Teste de autenticação',
                'payment_method' => 'pix',
            ]);
        
        $this->checkResponse($response3, 'X-API-Key (privada)');
        $this->newLine();

        // Teste 4: Authorization Bearer com chave privada
        $this->line('Teste 4: Authorization Bearer com chave privada');
        $response4 = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $privateKey,
            ])
            ->post($endpoint, [
                'amount' => $amount,
                'currency' => 'BRL',
                'description' => 'Teste de autenticação',
                'payment_method' => 'pix',
            ]);
        
        $this->checkResponse($response4, 'Authorization Bearer (privada)');
        $this->newLine();

        // Teste 5: Ambas as chaves (X-Public-Key e X-Private-Key)
        $this->line('Teste 5: X-Public-Key e X-Private-Key');
        $response5 = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Public-Key' => $publicKey,
                'X-Private-Key' => $privateKey,
            ])
            ->post($endpoint, [
                'amount' => $amount,
                'currency' => 'BRL',
                'description' => 'Teste de autenticação',
                'payment_method' => 'pix',
            ]);
        
        $this->checkResponse($response5, 'X-Public-Key + X-Private-Key');
        $this->newLine();

        return 0;
    }

    private function checkResponse($response, string $method): void
    {
        $status = $response->status();
        $body = $response->body();
        $isHtml = str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html');
        $isJson = !$isHtml && !empty($response->json());

        if ($response->successful() && $isJson) {
            $this->info("  ✓ SUCESSO com {$method}!");
            $data = $response->json();
            $this->line('  Resposta: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } elseif ($isHtml) {
            $this->warn("  ✗ Retornou HTML (página de login) - Status: {$status}");
            $this->line('  Isso indica que a autenticação não funcionou');
        } else {
            $this->error("  ✗ Erro - Status: {$status}");
            $this->line('  Resposta: ' . substr($body, 0, 200));
        }
    }
}

