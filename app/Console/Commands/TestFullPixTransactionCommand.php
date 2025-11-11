<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use App\Models\User;
use App\Services\FullPixService;
use Illuminate\Console\Command;

class TestFullPixTransactionCommand extends Command
{
    protected $signature = 'fullpix:test-transaction {amount=10.00} {email=admin12@gmail.com}';

    protected $description = 'Testa a geração de um PIX via FullPix e verifica se aparece nas transações';

    public function handle(FullPixService $fullPixService): int
    {
        $amount = (float) $this->argument('amount');
        $email = $this->argument('email');
        
        $this->info("🧪 Testando geração de PIX FullPix de R$ " . number_format($amount, 2, ',', '.'));
        $this->newLine();

        // Verificar adquirente
        $acquirer = Acquirer::where('slug', 'fullpix')->first();
        
        if (!$acquirer) {
            $this->error('❌ Adquirente FullPix não encontrada no banco de dados.');
            return 1;
        }

        $this->line('📋 Status da adquirente:');
        $this->line('  - Ativa: ' . ($acquirer->is_active ? '✅ Sim' : '❌ Não'));
        $this->line('  - API Status: ' . $acquirer->api_status);
        $this->line('  - Secret Key: ' . (!empty($acquirer->credentials['secret_key']) ? substr($acquirer->credentials['secret_key'], 0, 20) . '...' : '❌ VAZIA'));
        $this->line('  - Public Key: ' . (!empty($acquirer->credentials['public_key']) ? substr($acquirer->credentials['public_key'], 0, 20) . '...' : '❌ VAZIA'));
        $this->newLine();

        if (!$acquirer->is_active) {
            $this->error('❌ Adquirente não está ativa! Ative-a no painel admin primeiro.');
            return 1;
        }

        if (!$fullPixService->isConfigured()) {
            $this->error('❌ API FullPix não está configurada (credenciais ausentes).');
            return 1;
        }

        // Buscar usuário
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuário não encontrado: {$email}");
            return 1;
        }

        $this->line("👤 Usuário: {$user->name} ({$user->email})");
        $this->newLine();

        // Gerar PIX
        $this->info('🔄 Gerando PIX...');
        
        $metadata = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user' => $user,
            'test' => true,
        ];

        $description = "Teste FullPix - PIX R$ " . number_format($amount, 2, ',', '.');
        
        $sale = $fullPixService->createTransaction($amount, $description, $metadata);

        if (!$sale) {
            $this->error('❌ Falha ao gerar PIX. Verifique os logs para mais detalhes.');
            return 1;
        }

        $this->info('✅ PIX gerado com sucesso!');
        $this->newLine();
        
        $transactionId = $sale['id'] ?? $sale['transaction_id'] ?? 'N/A';
        $this->line('📝 ID da transação: ' . $transactionId);
        
        if (isset($sale['pix']['qrcode'])) {
            $this->line('✅ QR Code gerado');
            $qrcode = $sale['pix']['qrcode'];
            $this->line('   Código PIX (primeiros 50 chars): ' . substr($qrcode, 0, 50) . '...');
        } else {
            $this->warn('⚠️ QR Code não encontrado na resposta');
        }

        $this->newLine();
        
        // Verificar se foi salvo no banco
        $fullpixSale = \App\Models\FullPixSale::where('fullpix_transaction_id', $transactionId)->first();
        
        if ($fullpixSale) {
            $this->info('✅ Venda salva no banco de dados!');
            $this->line('   - Status: ' . $fullpixSale->status);
            $this->line('   - Valor: R$ ' . number_format((float) $fullpixSale->amount, 2, ',', '.'));
            $this->line('   - User ID: ' . $fullpixSale->user_id);
        } else {
            $this->error('❌ Venda NÃO foi salva no banco de dados!');
            return 1;
        }

        $this->newLine();
        
        // Verificar se aparece nas transações
        $this->info('🔍 Verificando se aparece nas transações...');
        
        // Simular busca de transações do seller
        $pendingFullPixSales = \App\Models\FullPixSale::where('user_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $found = $pendingFullPixSales->contains(function ($sale) use ($transactionId) {
            return $sale->fullpix_transaction_id === $transactionId;
        });
        
        if ($found) {
            $this->info('✅ Venda encontrada na lista de transações pendentes!');
        } else {
            $this->warn('⚠️ Venda NÃO encontrada na lista de transações pendentes.');
            $this->line('   Total de vendas pendentes encontradas: ' . $pendingFullPixSales->count());
        }

        $this->newLine();
        $this->info('🎉 Teste concluído!');
        $this->line('   Verifique manualmente nas áreas de transações do seller e admin.');
        
        return 0;
    }
}

