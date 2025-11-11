<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\User\Sale\GenerateChargeController;
use App\Models\Acquirer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestFullPixCompleteCommand extends Command
{
    protected $signature = 'fullpix:test-complete {amount=10.00} {email=admin12@gmail.com}';

    protected $description = 'Testa o fluxo completo: gera PIX via controller e verifica se aparece nas transações';

    public function handle(): int
    {
        $amount = (float) $this->argument('amount');
        $email = $this->argument('email');
        
        $this->info("🧪 Teste Completo FullPix");
        $this->info("==========================");
        $this->newLine();

        // 1. Verificar adquirente
        $this->info('1️⃣ Verificando adquirente FullPix...');
        $acquirer = Acquirer::where('slug', 'fullpix')->first();
        
        if (!$acquirer || !$acquirer->is_active) {
            $this->error('❌ Adquirente FullPix não está ativa!');
            return 1;
        }

        $this->info('   ✅ Adquirente ativa');
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

        // 3. Verificar vendas antes
        $this->info('3️⃣ Verificando vendas antes do teste...');
        $salesBefore = \App\Models\FullPixSale::where('user_id', $user->id)->count();
        $pendingBefore = \App\Models\FullPixSale::where('user_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->count();
        
        $this->info("   📊 Total de vendas: {$salesBefore}");
        $this->info("   ⏳ Pendentes: {$pendingBefore}");
        $this->newLine();

        // 4. Autenticar usuário
        $this->info('4️⃣ Autenticando usuário...');
        Auth::login($user);
        $this->info('   ✅ Usuário autenticado');
        $this->newLine();

        // 5. Criar requisição simulada
        $this->info("5️⃣ Gerando PIX de R$ " . number_format($amount, 2, ',', '.') . " via controller...");
        
        $request = Request::create('/sale/generate-charge', 'POST', [
            'amount' => (string) $amount,
            'description' => 'Teste Completo FullPix',
        ]);
        
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // 6. Chamar controller
        try {
            $controller = app(GenerateChargeController::class);
            $response = $controller($request);
            
            $responseData = json_decode($response->getContent(), true);
            
            if ($response->getStatusCode() !== 200 || !isset($responseData['status']) || $responseData['status'] !== 'success') {
                $this->error('❌ Falha ao gerar PIX via controller');
                $this->error('   Resposta: ' . $response->getContent());
                return 1;
            }

            $transactionId = $responseData['sale_id'] ?? null;
            if (!$transactionId) {
                $this->error('❌ ID da transação não encontrado na resposta');
                return 1;
            }

            $this->info('   ✅ PIX gerado com sucesso!');
            $this->info("   📝 ID: {$transactionId}");
            
            if (isset($responseData['pix_qrcode'])) {
                $this->info('   ✅ QR Code gerado');
            }
            $this->newLine();
        } catch (\Exception $e) {
            $this->error('❌ Erro ao gerar PIX: ' . $e->getMessage());
            $this->error('   Trace: ' . $e->getTraceAsString());
            return 1;
        }

        // 7. Verificar se foi salvo no banco
        $this->info('6️⃣ Verificando se foi salvo no banco...');
        
        sleep(1); // Aguardar um pouco
        
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

        // 8. Verificar se aparece nas transações do seller
        $this->info('7️⃣ Verificando se aparece nas transações do seller...');
        
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

        // 9. Verificar se aparece nas transações do admin
        $this->info('8️⃣ Verificando se aparece nas transações do admin...');
        
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

        // 10. Verificar vendas após
        $this->info('9️⃣ Verificando vendas após o teste...');
        $salesAfter = \App\Models\FullPixSale::where('user_id', $user->id)->count();
        $pendingAfter = \App\Models\FullPixSale::where('user_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->count();
        
        $this->info("   📊 Total de vendas: {$salesAfter} (antes: {$salesBefore})");
        $this->info("   ⏳ Pendentes: {$pendingAfter} (antes: {$pendingBefore})");
        
        if ($salesAfter > $salesBefore) {
            $this->info('   ✅ Nova venda foi criada!');
        }
        $this->newLine();

        // Resumo
        $this->info('📋 Resumo do Teste');
        $this->info('==================');
        $this->line("✅ PIX gerado via controller: SIM");
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
            if (!$foundInSeller) {
                $this->warn('   - Venda não aparece nas transações do seller');
            }
            if (!$foundInAdmin) {
                $this->warn('   - Venda não aparece nas transações do admin');
            }
            return 1;
        }
    }
}

