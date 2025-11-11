<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use App\Services\LiberpayService;
use Illuminate\Console\Command;

class TestLiberpayActivationCommand extends Command
{
    protected $signature = 'liberpay:test-activation';
    protected $description = 'Testa a ativação e verificação da API Liberpay';

    public function handle(LiberpayService $liberpayService): int
    {
        $this->info('🔍 Verificando configuração da Liberpay...');
        $this->newLine();

        // 1. Verificar se a adquirente existe
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if (!$acquirer) {
            $this->error('❌ Adquirente Liberpay não encontrada no banco de dados.');
            return 1;
        }

        $this->info('✓ Adquirente encontrada: ' . $acquirer->name);
        $this->line('  - is_active: ' . ($acquirer->is_active ? '✅ Sim' : '❌ Não'));
        $this->line('  - api_status: ' . $acquirer->api_status);
        $this->newLine();

        // 2. Verificar credenciais
        $hasPublicKey = !empty($acquirer->credentials['chave_publica']);
        $hasPrivateKey = !empty($acquirer->credentials['chave_privada']);
        
        $this->info('🔑 Verificando credenciais...');
        $this->line('  - Chave Pública: ' . ($hasPublicKey ? '✅ Configurada' : '❌ Não configurada'));
        if ($hasPublicKey) {
            $this->line('    Valor: ' . substr($acquirer->credentials['chave_publica'], 0, 20) . '...');
        }
        
        $this->line('  - Chave Privada: ' . ($hasPrivateKey ? '✅ Configurada' : '❌ Não configurada'));
        if ($hasPrivateKey) {
            $this->line('    Valor: ' . substr($acquirer->credentials['chave_privada'], 0, 20) . '...');
        }
        $this->newLine();

        if (!$hasPublicKey || !$hasPrivateKey) {
            $this->error('❌ Credenciais não configuradas completamente.');
            $this->warn('   Configure as credenciais em Admin → Sistema → Adquirentes → Configurar Credenciais');
            return 1;
        }

        // 3. Verificar se está configurada no serviço
        if (!$liberpayService->isConfigured()) {
            $this->error('❌ API não está configurada no serviço.');
            return 1;
        }

        $this->info('✓ API configurada no serviço');
        $this->newLine();

        // 4. Ativar a adquirente se não estiver ativa
        if (!$acquirer->is_active) {
            $this->warn('⚠ Adquirente não está ativa. Ativando...');
            $acquirer->is_active = true;
            $acquirer->save();
            $this->info('✓ Adquirente ativada');
            $this->newLine();
        }

        // 5. Verificar status da API
        $this->info('🌐 Verificando status da API...');
        $this->line('  Aguarde...');
        
        try {
            $apiStatus = $liberpayService->checkApiStatus();
            
            $acquirer->refresh();
            $acquirer->api_status = $apiStatus;
            $acquirer->save();
            
            $this->newLine();
            
            switch ($apiStatus) {
                case 'online':
                    $this->info('✅ API está ONLINE e funcionando!');
                    $this->line('   A adquirente está pronta para gerar PIX.');
                    break;
                case 'offline':
                    $this->warn('⚠ API está OFFLINE');
                    $this->line('   Verifique se as credenciais estão corretas.');
                    break;
                case 'error':
                    $this->error('❌ Erro ao verificar API');
                    $this->line('   Verifique os logs em storage/logs/laravel.log');
                    $this->line('   Possíveis causas:');
                    $this->line('   - Credenciais incorretas');
                    $this->line('   - URL da API incorreta');
                    $this->line('   - Problema de conexão');
                    break;
                default:
                    $this->warn('⚠ Status desconhecido: ' . $apiStatus);
            }
            
            $this->newLine();
            $this->line('Status atualizado no banco de dados: ' . $apiStatus);
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao verificar status: ' . $e->getMessage());
            $this->line('   Detalhes: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }

        // 6. Testar criação de venda se API estiver online
        if ($apiStatus === 'online') {
            $this->newLine();
            $this->info('🧪 Testando criação de venda PIX de R$ 10,00...');
            
            try {
                $sale = $liberpayService->createSale(
                    10.00,
                    'Teste de ativação - PIX R$ 10,00',
                    [
                        'user_id' => 1,
                        'user_email' => 'admin12@gmail.com',
                        'test' => true,
                    ]
                );

                if ($sale) {
                    $this->info('✅ Venda criada com sucesso!');
                    $this->line('   ID da venda: ' . ($sale['id'] ?? 'N/A'));
                    
                    if (isset($sale['pix']['qrcode'])) {
                        $this->line('   QR Code gerado: ✅');
                        $this->line('   Código PIX: ' . substr($sale['pix']['qrcode'], 0, 50) . '...');
                    }
                    
                    $this->newLine();
                    $this->info('🎉 Tudo funcionando! A adquirente está pronta para uso.');
                } else {
                    $this->error('❌ Falha ao criar venda.');
                    $this->line('   Verifique os logs para mais detalhes.');
                }
            } catch (\Exception $e) {
                $this->error('❌ Erro ao criar venda: ' . $e->getMessage());
            }
        } else {
            $this->warn('⚠ Pulando teste de criação de venda (API não está online)');
        }

        return 0;
    }
}

