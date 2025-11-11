<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\LiberpaySale;
use App\Models\Transaction;
use App\Services\FeeCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiberpayWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('Liberpay Webhook: Recebido', ['data' => $data]);

            // Extrair ID da venda da resposta
            $saleId = $data['id'] ?? $data['sale_id'] ?? $data['transaction_id'] ?? null;

            if (!$saleId) {
                Log::warning('Liberpay Webhook: ID da venda não encontrado', ['data' => $data]);
                return response()->json(['error' => 'ID da venda não encontrado'], 400);
            }

            // Buscar venda no banco
            $liberpaySale = LiberpaySale::where('liberpay_sale_id', (string) $saleId)->first();

            if (!$liberpaySale) {
                Log::warning('Liberpay Webhook: Venda não encontrada no banco', ['sale_id' => $saleId]);
                return response()->json(['error' => 'Venda não encontrada'], 404);
            }

            $oldStatus = $liberpaySale->status;

            // Atualizar status da venda
            $status = $data['status'] ?? $data['payment_status'] ?? $liberpaySale->status;
            
            // Mapear status da API para status interno
            $statusMap = [
                'paid' => 'paid',
                'pago' => 'paid',
                'approved' => 'paid',
                'pending' => 'pending',
                'pendente' => 'pending',
                'expired' => 'expired',
                'expirado' => 'expired',
                'cancelled' => 'cancelled',
                'cancelado' => 'cancelled',
                'refunded' => 'refunded',
                'reembolsado' => 'refunded',
            ];
            
            $statusLower = strtolower((string) $status);
            if (isset($statusMap[$statusLower])) {
                $liberpaySale->status = $statusMap[$statusLower];
            } elseif ($status) {
                // Se o status não estiver no mapa, tentar usar diretamente
                $liberpaySale->status = $statusLower;
            }

            // Se foi pago, atualizar data de pagamento
            $statusLower = strtolower((string) $status);
            if (in_array($statusLower, ['paid', 'pago', 'approved']) && !$liberpaySale->paid_at) {
                $liberpaySale->paid_at = now();
                $liberpaySale->status = 'paid';
                
                // Criar transação para o usuário
                $this->createTransaction($liberpaySale);
                
                Log::info('Liberpay Webhook: Pagamento confirmado e transação criada', [
                    'sale_id' => $saleId,
                    'user_id' => $liberpaySale->user_id,
                ]);
            }

            // Salvar resposta completa da API
            $liberpaySale->liberpay_response = $data;
            
            // IMPORTANTE: Preservar metadata (incluindo form_data) ao salvar
            // O metadata já contém form_data do checkout e não deve ser sobrescrito
            $liberpaySale->save();

            Log::info('Liberpay Webhook: Venda atualizada', [
                'sale_id' => $saleId,
                'old_status' => $oldStatus,
                'new_status' => $liberpaySale->status,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Liberpay Webhook: Erro ao processar', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro ao processar webhook'], 500);
        }
    }

    /**
     * Cria uma transação quando o pagamento é confirmado
     */
    private function createTransaction(LiberpaySale $liberpaySale): void
    {
        $user = $liberpaySale->user;

        // Verificar se já existe transação para esta venda
        $existingTransaction = Transaction::where('acquirer_ref', $liberpaySale->liberpay_sale_id)->first();

        if ($existingTransaction) {
            return;
        }

        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $liberpaySale->amount;
        
        // Buscar adquirente LiberPay para calcular taxas corretas
        $liberpayAcquirer = \App\Models\Acquirer::where('slug', 'liberpay')->first();
        
        // Calcular taxas (LiberPay + LuckPay configurável)
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $user, $liberpayAcquirer);
        
        $acquirerFee = $feeCalculation['acquirer_fee'];
        $gatewayFee = $feeCalculation['gateway_fee'];
        $totalFees = $feeCalculation['total_fees'];
        $netDeposit = $feeCalculation['net_amount'];

        // Extrair product_id do metadata se disponível (vindo de checkout)
        $productId = $liberpaySale->metadata['product_id'] ?? $liberpaySale->metadata['checkout_product_id'] ?? null;

        // Criar nova transação
        Transaction::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'invoice' => 'LIB-' . $liberpaySale->liberpay_sale_id,
            'payment_status' => 'Paid',
            'total_amount' => $totalAmount,
            'payment_method' => 'PIX',
            'net_deposit' => $netDeposit, // Valor líquido após desconto de todas as taxas
            'acquirer_ref' => $liberpaySale->liberpay_sale_id,
            'date' => today(), // Usar today() para garantir apenas a data sem hora
            'fee' => $gatewayFee, // Apenas taxa da gateway (LuckPay), não inclui taxa da LiberPay
            'is_sample' => false,
        ]);

        // Atualizar saldo do usuário com o valor líquido (após desconto da comissão)
        $user->increment('balance', $netDeposit);
    }
}
