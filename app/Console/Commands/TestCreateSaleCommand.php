<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use App\Services\LiberpayService;
use Illuminate\Console\Command;

class TestCreateSaleCommand extends Command
{
    protected $signature = 'liberpay:test-create-sale {amount=10.00}';

    protected $description = 'Testa a criação de uma venda PIX na API Liberpay';

    public function handle(LiberpayService $liberpayService): int
    {
        $amount = (float) $this->argument('amount');
        
        $this->info("Testando criação de venda PIX de R$ " . number_format($amount, 2, ',', '.'));
        $this->newLine();

        // Verificar se está configurada
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if (!$acquirer) {
            $this->error('Adquirente Liberpay não encontrada no banco de dados.');
            return 1;
        }

        $this->line('Status da adquirente:');
        $this->line('  - Ativa: ' . ($acquirer->is_active ? 'Sim' : 'Não'));
        $this->line('  - API Status: ' . $acquirer->api_status);
        $this->line('  - Chave Pública: ' . (!empty($acquirer->credentials['chave_publica']) ? substr($acquirer->credentials['chave_publica'], 0, 20) . '...' : 'VAZIA'));
        $this->line('  - Chave Privada: ' . (!empty($acquirer->credentials['chave_privada']) ? substr($acquirer->credentials['chave_privada'], 0, 20) . '...' : 'VAZIA'));
        $this->newLine();

        // Se não estiver ativa, ativar automaticamente para teste
        if (!$acquirer->is_active) {
            $this->warn('Adquirente não está ativa! Ativando automaticamente para teste...');
            $acquirer->is_active = true;
            $acquirer->save();
        }

        if (!$liberpayService->isConfigured()) {
            $this->error('API não está configurada (chaves faltando)!');
            $this->line('Configure as chaves no admin: /admin/acquirers');
            return 1;
        }

        $this->info('Criando venda na API...');
        $this->newLine();

        try {
            // Criar usuário mock para teste
            $mockUser = (object) [
                'id' => 1,
                'name' => 'Teste Usuário',
                'email' => 'teste@luckpay.com',
                'phone' => '11999999999',
                'document' => '12345678901',
            ];
            
            $sale = $liberpayService->createSale(
                $amount,
                "Teste de depósito de R$ " . number_format($amount, 2, ',', '.'),
                [
                    'test' => true,
                    'user_id' => 1,
                    'user' => $mockUser,
                ]
            );

            if (!$sale) {
                $this->error('Falha ao criar venda. Verifique os logs em storage/logs/laravel.log');
                $this->line('Últimas linhas do log:');
                $this->line('----------------------------------------');
                $logFile = storage_path('logs/laravel.log');
                if (file_exists($logFile)) {
                    $lines = file($logFile);
                    $lastLines = array_slice($lines, -10);
                    foreach ($lastLines as $line) {
                        $this->line(trim($line));
                    }
                }
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Exceção ao criar venda: ' . $e->getMessage());
            $this->line('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }

        $this->info('✓ Venda criada com sucesso!');
        $this->newLine();
        $this->line('Resposta da API:');
        $this->line(json_encode($sale, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->newLine();

        // Verificar se tem QR code
        // A API retorna: pix: { qrcode: "..." }
        $hasQrCode = false;
        $qrCodeContent = null;

        if (isset($sale['pix']['qrcode'])) {
            $hasQrCode = true;
            $qrCodeContent = $sale['pix']['qrcode'];
        } elseif (isset($sale['pix']['qr_code'])) {
            $hasQrCode = true;
            $qrCodeContent = $sale['pix']['qr_code'];
        } elseif (isset($sale['pix']['content'])) {
            $hasQrCode = true;
            $qrCodeContent = $sale['pix']['content'];
        } elseif (isset($sale['qr_code'])) {
            $hasQrCode = true;
            $qrCodeContent = $sale['qr_code'];
        } elseif (isset($sale['pix_code'])) {
            $hasQrCode = true;
            $qrCodeContent = $sale['pix_code'];
        }

        if ($hasQrCode) {
            $this->info('✓ QR Code encontrado!');
            $this->line('QR Code (primeiros 50 caracteres): ' . substr($qrCodeContent, 0, 50) . '...');
        } else {
            $this->warn('⚠ QR Code não encontrado na resposta!');
            $this->line('Estrutura da resposta pode estar diferente do esperado.');
        }

        return 0;
    }
}

