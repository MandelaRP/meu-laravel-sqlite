<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestLiberpayEndpointsCommand extends Command
{
    protected $signature = 'liberpay:test-endpoints';

    protected $description = 'Testa diferentes endpoints da API Liberpay';

    public function handle(): int
    {
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if (!$acquirer) {
            $this->error('Adquirente não encontrada');
            return 1;
        }

        $publicKey = $acquirer->credentials['chave_publica'] ?? null;

        if (!$publicKey) {
            $this->error('Chave pública não configurada');
            return 1;
        }

        $baseUrl = 'https://app.liberpay.pro';
        $amount = 10.00;

        $endpoints = [
            "{$baseUrl}/v1/sales",
            "{$baseUrl}/v1/sale",
            "{$baseUrl}/api/v1/sales",
            "{$baseUrl}/api/sales",
            "{$baseUrl}/sales",
        ];

        $this->info('Testando diferentes endpoints...');
        $this->newLine();

        foreach ($endpoints as $endpoint) {
            $this->line("Testando: {$endpoint}");
            
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-API-Key' => $publicKey,
                ])
                ->post($endpoint, [
                    'amount' => $amount,
                    'currency' => 'BRL',
                    'description' => 'Teste',
                    'payment_method' => 'pix',
                ]);

            $status = $response->status();
            $body = $response->body();
            $isHtml = str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html');
            $isJson = !$isHtml && !empty($response->json());

            if ($response->successful() && $isJson) {
                $this->info("  ✓ SUCESSO! Endpoint correto: {$endpoint}");
                $data = $response->json();
                $this->line('  Resposta: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return 0;
            } elseif ($isHtml) {
                $this->warn("  ✗ Retornou HTML - Status: {$status}");
            } else {
                $this->error("  ✗ Erro - Status: {$status}");
                $this->line('  Resposta: ' . substr($body, 0, 200));
            }
            
            $this->newLine();
        }

        $this->error('Nenhum endpoint funcionou. Verifique a documentação oficial.');
        return 1;
    }
}

