<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\FullPixSale;
use App\Models\Transaction;
use App\Services\FeeCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FullPixWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('FullPix Webhook: Recebido', ['data' => $data]);

            // A estrutura do webhook FullPix pode ser:
            // { event: "...", timestamp: "...", withdrawal: {...} } para saques
            // ou { id: "...", status: "...", ... } para transações
            // ou { data: { id: "...", status: "...", ... } } para webhooks de transação
            
            // Verificar se é webhook de saque (withdrawal)
            if (isset($data['withdrawal']) || isset($data['withdrawal_id'])) {
                return $this->handleWithdrawalWebhook($data);
            }
            
            // Extrair ID da transação
            $transactionId = null;
            $status = null;
            
            // Verificar se é estrutura de webhook de transação (com 'data')
            if (isset($data['data']) && is_array($data['data'])) {
                $transactionId = $data['data']['id'] ?? null;
                $status = $data['data']['status'] ?? null;
            } else {
                // Estrutura direta
                $transactionId = $data['id'] ?? $data['transaction_id'] ?? null;
                $status = $data['status'] ?? null;
            }

            if (!$transactionId) {
                Log::warning('FullPix Webhook: ID da transação não encontrado', ['data' => $data]);
                return response()->json(['error' => 'ID da transação não encontrado'], 400);
            }

            // Buscar transação no banco
            $fullpixSale = FullPixSale::where('fullpix_transaction_id', (string) $transactionId)->first();

            if (!$fullpixSale) {
                Log::warning('FullPix Webhook: Transação não encontrada no banco', ['transaction_id' => $transactionId]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            $oldStatus = $fullpixSale->status;

            // Mapear status da API para status interno
            $statusMap = [
                'paid' => 'paid',
                'waiting_payment' => 'waiting_payment',
                'pending' => 'pending',
                'refused' => 'refused',
                'cancelled' => 'cancelled',
                'refunded' => 'refunded',
                'expired' => 'expired',
            ];
            
            $statusLower = strtolower((string) $status);
            if (isset($statusMap[$statusLower])) {
                $fullpixSale->status = $statusMap[$statusLower];
            } elseif ($status) {
                $fullpixSale->status = $statusLower;
            }

            // Se foi pago, atualizar data de pagamento
            if (in_array($statusLower, ['paid']) && !$fullpixSale->paid_at) {
                $fullpixSale->paid_at = now();
                $fullpixSale->status = 'paid';
                
                // Criar transação para o usuário
                $this->createTransaction($fullpixSale);
                
                Log::info('FullPix Webhook: Pagamento confirmado e transação criada', [
                    'transaction_id' => $transactionId,
                    'user_id' => $fullpixSale->user_id,
                ]);
            }

            // Salvar resposta completa da API
            $fullpixSale->fullpix_response = $data;
            
            // IMPORTANTE: Preservar metadata (incluindo form_data) ao salvar
            // O metadata já contém form_data do checkout e não deve ser sobrescrito
            $fullpixSale->save();

            Log::info('FullPix Webhook: Transação atualizada', [
                'transaction_id' => $transactionId,
                'old_status' => $oldStatus,
                'new_status' => $fullpixSale->status,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('FullPix Webhook: Erro ao processar', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro ao processar webhook'], 500);
        }
    }

    /**
     * Cria uma transação quando o pagamento é confirmado
     */
    private function createTransaction(FullPixSale $fullpixSale): void
    {
        $user = $fullpixSale->user;

        // Verificar se já existe transação para esta venda
        $existingTransaction = Transaction::where('acquirer_ref', $fullpixSale->fullpix_transaction_id)->first();

        if ($existingTransaction) {
            return;
        }

        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $fullpixSale->amount;
        
        // Buscar adquirente FullPix para calcular taxas corretas
        $fullpixAcquirer = \App\Models\Acquirer::where('slug', 'fullpix')->first();
        
        // Calcular taxas (FullPix + LuckPay configurável)
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $user, $fullpixAcquirer);
        
        $gatewayFee = $feeCalculation['gateway_fee'];
        $netDeposit = $feeCalculation['net_amount'];

        // Extrair product_id do metadata se disponível (vindo de checkout)
        $productId = $fullpixSale->metadata['product_id'] ?? $fullpixSale->metadata['checkout_product_id'] ?? null;

        // Criar nova transação
        Transaction::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'invoice' => 'FPX-' . $fullpixSale->fullpix_transaction_id,
            'payment_status' => 'Paid',
            'total_amount' => $totalAmount,
            'payment_method' => 'PIX',
            'net_deposit' => $netDeposit, // Valor líquido após desconto de todas as taxas
            'acquirer_ref' => $fullpixSale->fullpix_transaction_id,
            'date' => today(), // Usar today() para garantir apenas a data sem hora
            'fee' => $gatewayFee, // Apenas taxa da gateway (LuckPay), não inclui taxa da FullPix
            'is_sample' => false,
        ]);

        // Atualizar saldo do usuário com o valor líquido (após desconto da comissão)
        $user->increment('balance', $netDeposit);
    }

    /**
     * Trata webhook de saque (withdrawal)
     */
    private function handleWithdrawalWebhook(array $data): JsonResponse
    {
        try {
            // Extrair dados do saque
            $withdrawalData = $data['withdrawal'] ?? $data;
            $withdrawalId = $withdrawalData['id'] ?? $withdrawalData['withdrawal_id'] ?? null;
            $status = $withdrawalData['status'] ?? null;

            if (!$withdrawalId) {
                Log::warning('FullPix Webhook: ID do saque não encontrado', ['data' => $data]);
                return response()->json(['error' => 'ID do saque não encontrado'], 400);
            }

            // Buscar saque no banco
            $withdrawal = \Illuminate\Support\Facades\DB::table('withdrawals')
                ->where('fullpix_withdrawal_id', (string) $withdrawalId)
                ->first();

            if (!$withdrawal) {
                Log::warning('FullPix Webhook: Saque não encontrado no banco', ['withdrawal_id' => $withdrawalId]);
                return response()->json(['error' => 'Saque não encontrado'], 404);
            }

            $oldStatus = $withdrawal->status;
            $statusLower = strtolower((string) $status);

            // Mapear status da API para status interno
            $statusMap = [
                'success' => 'done',
                'done' => 'done',
                'approved' => 'done',
                'completed' => 'done',
                'paid' => 'done',
                'processing' => 'processing',
                'pending' => 'pending',
                'failed' => 'failed',
                'refused' => 'refused',
                'cancelled' => 'cancelled',
            ];

            $newStatus = $statusMap[$statusLower] ?? $statusLower;

            // Atualizar saque
            $updateData = [
                'status' => $newStatus,
                'fullpix_response' => json_encode($data),
                'updated_at' => now(),
            ];

            // Se foi confirmado, atualizar paid_at
            if (in_array($newStatus, ['done']) && !$withdrawal->paid_at) {
                $updateData['paid_at'] = now();
            }

            \Illuminate\Support\Facades\DB::table('withdrawals')
                ->where('id', $withdrawal->id)
                ->update($updateData);

            Log::info('FullPix Webhook: Saque atualizado', [
                'withdrawal_id' => $withdrawalId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('FullPix Webhook: Erro ao processar saque', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro ao processar webhook de saque'], 500);
        }
    }
}
