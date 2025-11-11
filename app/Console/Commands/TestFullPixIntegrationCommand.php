<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use App\Models\User;
use App\Services\FullPixService;
use Illuminate\Console\Command;

class TestFullPixIntegrationCommand extends Command
{
    protected $signature = 'fullpix:test-integration {amount=10.00} {email=admin12@gmail.com}';

    protected $description = 'Testa a integração completa do FullPix: gera PIX e verifica se aparece nas transações';

    public function handle(FullPixService $fullPixService): int
    {
        $amount = (float) $this->argument('amount');
        $email = $this->argument('email');
        
        $this->info("🧪 Teste de Integração FullPix");
        $this->info("=================================");
        $this->newLine();

        // 1. Verificar adquirente
        $this->info('1️⃣ Verificando adquirente FullPix...');
        $acquirer = Acquirer::where('slug', 'fullpix')->first();
        
        if (!$acquirer) {
            $this->error('❌ Adquirente FullPix não encontrada.');
            return 1;
        }

        if (!$acquirer->is_active) {
            $this->error('❌ Adquirente não está ativa!');
            return 1;
        }

        $this->info('   ✅ Adquirente ativa');
        $this->info('   📊 Status API: ' . $acquirer->api_status);
        $this->newLine();

        // 2. Buscar usuário
        $this->info('2️⃣ Buscando usuário...');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuário não encontrado: {$email}");
            return 1;
        }

        $this->info("   ✅ Usuário: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // 3. Verificar vendas existentes antes
        $this->info('3️⃣ Verificando vendas existentes...');
        $salesBefore = \App\Models\FullPixSale::where('user_id', $user->id)->count();
        $pendingBefore = \App\Models\FullPixSale::where('user_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->count();
        
        $this->info("   📊 Total de vendas: {$salesBefore}");
        $this->info("   ⏳ Pendentes sem transação: {$pendingBefore}");
        $this->newLine();

        // 4. Gerar PIX
        $this->info("4️⃣ Gerando PIX de R$ " . number_format($amount, 2, ',', '.') . "...");
        
        $metadata = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user' => $user,
            'test' => true,
        ];

        $description = "Teste de Integração - PIX R$ " . number_format($amount, 2, ',', '.');
        
        $sale = $fullPixService->createTransaction($amount, $description, $metadata);

        if (!$sale) {
            $this->error('❌ Falha ao gerar PIX.');
            return 1;
        }

        $transactionId = $sale['id'] ?? $sale['transaction_id'] ?? null;
        if (!$transactionId) {
            $this->error('❌ ID da transação não encontrado na resposta.');
            return 1;
        }

        $this->info('   ✅ PIX gerado com sucesso!');
        $this->info("   📝 ID: {$transactionId}");
        
        if (isset($sale['pix']['qrcode'])) {
            $this->info('   ✅ QR Code gerado');
        }
        $this->newLine();

        // 5. Verificar se foi salvo no banco
        $this->info('5️⃣ Verificando se foi salvo no banco...');
        
        // Aguardar um pouco para garantir que foi salvo
        sleep(1);
        
        $fullpixSale = \App\Models\FullPixSale::where('fullpix_transaction_id', $transactionId)->first();
        
        if (!$fullpixSale) {
            $this->error('❌ Venda NÃO foi salva no banco de dados!');
            $this->warn('   Verifique os logs para mais detalhes.');
            return 1;
        }

        $this->info('   ✅ Venda salva no banco!');
        $this->info("   📊 Status: {$fullpixSale->status}");
        $this->info("   💰 Valor: R$ " . number_format((float) $fullpixSale->amount, 2, ',', '.'));
        $this->newLine();

        // 6. Verificar se aparece nas transações do seller
        $this->info('6️⃣ Verificando se aparece nas transações do seller...');
        
        $pendingFullPixSales = \App\Models\FullPixSale::where('user_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $foundInSeller = $pendingFullPixSales->contains(function ($sale) use ($transactionId) {
            return $sale->fullpix_transaction_id === $transactionId;
        });
        
        if ($foundInSeller) {
            $this->info('   ✅ Venda encontrada na lista de transações pendentes do seller!');
        } else {
            $this->error('   ❌ Venda NÃO encontrada na lista de transações pendentes do seller!');
            $this->warn("   Total de pendentes encontradas: {$pendingFullPixSales->count()}");
            $this->warn("   Status da venda: {$fullpixSale->status}");
        }
        $this->newLine();

        // 7. Verificar se aparece nas transações do admin
        $this->info('7️⃣ Verificando se aparece nas transações do admin...');
        
        $pendingFullPixSalesAdmin = \App\Models\FullPixSale::with('user')
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $foundInAdmin = $pendingFullPixSalesAdmin->contains(function ($sale) use ($transactionId) {
            return $sale->fullpix_transaction_id === $transactionId;
        });
        
        if ($foundInAdmin) {
            $this->info('   ✅ Venda encontrada na lista de transações pendentes do admin!');
        } else {
            $this->error('   ❌ Venda NÃO encontrada na lista de transações pendentes do admin!');
            $this->warn("   Total de pendentes encontradas: {$pendingFullPixSalesAdmin->count()}");
        }
        $this->newLine();

        // 8. Verificar vendas após
        $this->info('8️⃣ Verificando vendas após o teste...');
        $salesAfter = \App\Models\FullPixSale::where('user_id', $user->id)->count();
        $pendingAfter = \App\Models\FullPixSale::where('user_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->count();
        
        $this->info("   📊 Total de vendas: {$salesAfter} (antes: {$salesBefore})");
        $this->info("   ⏳ Pendentes sem transação: {$pendingAfter} (antes: {$pendingBefore})");
        
        if ($salesAfter > $salesBefore) {
            $this->info('   ✅ Nova venda foi criada!');
        }
        $this->newLine();

        // Resumo
        $this->info('📋 Resumo do Teste');
        $this->info('==================');
        $this->line("✅ PIX gerado: " . ($sale ? 'SIM' : 'NÃO'));
        $this->line("✅ Salvo no banco: " . ($fullpixSale ? 'SIM' : 'NÃO'));
        $this->line("✅ Aparece no seller: " . ($foundInSeller ? 'SIM' : 'NÃO'));
        $this->line("✅ Aparece no admin: " . ($foundInAdmin ? 'SIM' : 'NÃO'));
        $this->newLine();

        if ($foundInSeller && $foundInAdmin) {
            $this->info('🎉 Teste concluído com SUCESSO!');
            $this->info('   A integração FullPix está funcionando corretamente.');
            return 0;
        } else {
            $this->error('⚠️ Teste concluído com AVISOS!');
            $this->warn('   Verifique os controllers de transações.');
            return 1;
        }
    }
}

