<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\Acquirer;
use App\Models\LiberpaySale;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateFakeSalesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:create-fake {--user-id= : ID do usuário (opcional)} {--count=5 : Número de vendas a criar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria vendas fictícias para testar o sistema';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user-id');
        $count = (int) $this->option('count');

        // Buscar ou criar usuário
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuário com ID {$userId} não encontrado!");
                return 1;
            }
        } else {
            // Buscar primeiro usuário que não seja admin
            $user = User::where('role', '!=', 'admin')
                ->where('is_sample', false)
                ->first();
            
            if (!$user) {
                $this->error('Nenhum usuário encontrado! Crie um usuário primeiro.');
                return 1;
            }
        }

        $this->info("Criando {$count} vendas fictícias para o usuário: {$user->name} ({$user->email})");

        // Buscar adquirente para obter a porcentagem de comissão
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        $gatewayFeePercentage = $acquirer?->gateway_fee_percentage ?? 2.99;

        $this->info("Taxa da gateway: {$gatewayFeePercentage}%");

        $saldoInicial = $user->balance ?? 0;
        $this->info("Saldo inicial: R$ " . number_format($saldoInicial, 2, ',', '.'));

        $valores = [10.00, 25.50, 50.00, 100.00, 200.00, 500.00, 1000.00];
        $totalNetDeposit = 0;

        for ($i = 0; $i < $count; $i++) {
            // Valor aleatório da lista
            $totalAmount = $valores[array_rand($valores)];
            
            // Calcular comissão
            $fee = ($totalAmount * $gatewayFeePercentage) / 100;
            $netDeposit = $totalAmount - $fee;
            $totalNetDeposit += $netDeposit;

            // Gerar ID fictício para a venda
            $liberpaySaleId = 'FAKE-' . time() . '-' . $i;

            // Criar LiberpaySale
            $liberpaySale = LiberpaySale::create([
                'user_id' => $user->id,
                'liberpay_sale_id' => $liberpaySaleId,
                'reference_code' => 'REF-' . $liberpaySaleId,
                'external_reference' => 'EXT-' . $liberpaySaleId,
                'amount' => $totalAmount,
                'currency' => 'BRL',
                'status' => 'paid',
                'pix_qr_code' => '00020101021226880014br.gov.bcb.pix2566qrcode.micro' . $liberpaySaleId,
                'pix_qr_code_image' => null,
                'expires_at' => now()->addDays(1),
                'paid_at' => now()->subHours(rand(1, 24)), // Pagamento em algum momento nas últimas 24h
                'metadata' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
                'liberpay_response' => [
                    'id' => $liberpaySaleId,
                    'status' => 'paid',
                    'amount' => $totalAmount,
                    'items' => [
                        [
                            'title' => 'Depósito',
                            'unitPrice' => $totalAmount * 100, // em centavos
                            'tangible' => false,
                        ],
                    ],
                    'pix' => [
                        'qrcode' => '00020101021226880014br.gov.bcb.pix2566qrcode.micro' . $liberpaySaleId,
                        'expirationDate' => now()->addDays(1)->toIso8601String(),
                    ],
                ],
            ]);

            // Criar Transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'invoice' => 'LIB-' . $liberpaySaleId,
                'payment_status' => 'Paid',
                'total_amount' => $totalAmount,
                'payment_method' => 'PIX',
                'net_deposit' => $netDeposit,
                'acquirer_ref' => $liberpaySaleId,
                'date' => $liberpaySale->paid_at,
                'fee' => $fee,
                'is_sample' => false,
            ]);

            $vendaNum = $i + 1;
            $this->line("✓ Venda #{$vendaNum}: R$ " . number_format($totalAmount, 2, ',', '.') . 
                        " (Taxa: R$ " . number_format($fee, 2, ',', '.') . 
                        " | Líquido: R$ " . number_format($netDeposit, 2, ',', '.') . ")");
        }

        // Atualizar saldo do usuário
        $user->increment('balance', $totalNetDeposit);
        $user->refresh();

        $saldoFinal = $user->balance ?? 0;
        $saldoAdicionado = $saldoFinal - $saldoInicial;

        $this->newLine();
        $this->info("=== Resumo ===");
        $this->info("Vendas criadas: {$count}");
        $this->info("Total líquido adicionado: R$ " . number_format($totalNetDeposit, 2, ',', '.'));
        $this->info("Saldo inicial: R$ " . number_format($saldoInicial, 2, ',', '.'));
        $this->info("Saldo final: R$ " . number_format($saldoFinal, 2, ',', '.'));
        $this->info("Saldo adicionado: R$ " . number_format($saldoAdicionado, 2, ',', '.'));

        // Verificar estatísticas
        $this->newLine();
        $this->info("=== Verificando Estatísticas ===");
        
        $faturamento = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->sum('net_deposit');
        
        $quantidade = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->count();
        
        $ticketMedio = Transaction::where('user_id', $user->id)
            ->where('payment_status', 'Paid')
            ->where('is_sample', false)
            ->avg('net_deposit') ?? 0;

        $this->info("Faturamento total: R$ " . number_format($faturamento, 2, ',', '.'));
        $this->info("Quantidade de vendas: {$quantidade}");
        $this->info("Ticket médio: R$ " . number_format($ticketMedio, 2, ',', '.'));

        Log::info('Vendas fictícias criadas', [
            'user_id' => $user->id,
            'count' => $count,
            'total_net_deposit' => $totalNetDeposit,
        ]);

        $this->newLine();
        $this->info("✓ Vendas fictícias criadas com sucesso!");
        $this->info("Acesse o dashboard e a área de transações para verificar.");

        return 0;
    }
}

