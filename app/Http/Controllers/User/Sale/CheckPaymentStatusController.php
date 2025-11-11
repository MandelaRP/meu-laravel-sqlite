<?php

declare(strict_types = 1);

namespace App\Http\Controllers\User\Sale;

use App\Http\Controllers\Controller;
use App\Models\LiberpaySale;
use App\Models\FullPixSale;
use App\Services\FeeCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPaymentStatusController extends Controller
{
    public function __construct(
        private readonly FeeCalculationService $feeCalculationService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $saleId): JsonResponse
    {
        $user = Auth::user();

        try {
            // Buscar venda no banco (pode ser Liberpay ou FullPix)
            $liberpaySale = LiberpaySale::where('liberpay_sale_id', $saleId)
                ->where('user_id', $user->id)
                ->first();
            
            $fullpixSale = null;
            if (!$liberpaySale) {
                $fullpixSale = FullPixSale::where('fullpix_transaction_id', $saleId)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$liberpaySale && !$fullpixSale) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Transação não encontrada',
                ], 404);
            }
            
            // Usar a venda encontrada
            $sale = $liberpaySale ?? $fullpixSale;
            $isFullPix = $fullpixSale !== null;

            // Se já está pago, retornar status
            if ($sale->status === 'paid' && $sale->paid_at) {
                // Verificar se já tem transação criada
                $acquirerRef = $isFullPix ? $fullpixSale->fullpix_transaction_id : $liberpaySale->liberpay_sale_id;
                $existingTransaction = \App\Models\Transaction::where('acquirer_ref', $acquirerRef)->first();
                if (!$existingTransaction) {
                    if ($isFullPix) {
                        $this->createTransactionFromFullPixSale($fullpixSale);
                    } else {
                        $this->createTransactionFromSale($liberpaySale);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'payment_status' => 'paid',
                    'message' => 'Pagamento confirmado',
                    'paid_at' => $sale->paid_at?->toIso8601String(),
                ]);
            }

            // Buscar status atualizado na API
            $saleData = $this->checkStatusInApi($saleId, $isFullPix);

            // Se não conseguiu verificar na API, usar status do banco mas continuar verificando
            if (!$saleData) {
                // Retornar status atual do banco
                return response()->json([
                    'status' => 'success',
                    'payment_status' => $sale->status,
                    'message' => $this->getStatusMessage($sale->status),
                ]);
            }

            // Atualizar status no banco com dados da API
            $oldStatus = $sale->status;
            $apiStatus = $saleData['status'] ?? $saleData['payment_status'] ?? $saleData['transaction_status'] ?? $sale->status;

            // Mapear status
            $statusMap = [
                'paid' => 'paid',
                'pago' => 'paid',
                'approved' => 'paid',
                'approved_payment' => 'paid',
                'pending' => 'pending',
                'pendente' => 'pending',
                'waiting_payment' => $isFullPix ? 'waiting_payment' : 'pending',
                'expired' => 'expired',
                'expirado' => 'expired',
                'cancelled' => 'cancelled',
                'cancelado' => 'cancelled',
            ];

            $statusLower = strtolower((string) $apiStatus);
            $mappedStatus = $statusMap[$statusLower] ?? $statusLower;

            // Atualizar status no banco
            if ($mappedStatus !== $sale->status) {
                $sale->status = $mappedStatus;
                
                // Se foi pago, atualizar data e criar transação
                if (in_array($mappedStatus, ['paid']) && !$sale->paid_at) {
                    $sale->paid_at = now();
                    $sale->status = 'paid';
                    
                    // Criar transação se ainda não existe
                    $acquirerRef = $isFullPix ? $fullpixSale->fullpix_transaction_id : $liberpaySale->liberpay_sale_id;
                    $existingTransaction = \App\Models\Transaction::where('acquirer_ref', $acquirerRef)->first();
                    if (!$existingTransaction) {
                        if ($isFullPix) {
                            $this->createTransactionFromFullPixSale($fullpixSale);
                        } else {
                            $this->createTransactionFromSale($liberpaySale);
                        }
                    }

                    $acquirerName = $isFullPix ? 'FullPix' : 'Liberpay';
                    Log::info("{$acquirerName}: Pagamento confirmado via polling", [
                        'sale_id' => $saleId,
                        'user_id' => $user->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'paid',
                    ]);
                }
                
                $sale->save();
            }

            return response()->json([
                'status' => 'success',
                'payment_status' => $sale->status,
                'message' => $this->getStatusMessage($sale->status),
                'paid_at' => $sale->paid_at?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status', [
                'sale_id' => $saleId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao verificar status do pagamento',
            ], 500);
        }
    }

    /**
     * Verifica status na API (Liberpay ou FullPix)
     */
    private function checkStatusInApi(string $saleId, bool $isFullPix = false): ?array
    {
        try {
            if ($isFullPix) {
                $fullPixService = app(\App\Services\FullPixService::class);
                $saleData = $fullPixService->findTransaction($saleId);
                
                if ($saleData) {
                    Log::info('FullPix: Status verificado na API', [
                        'transaction_id' => $saleId,
                        'status' => $saleData['status'] ?? 'unknown',
                    ]);
                    return $saleData;
                }
            } else {
                $liberpayService = app(\App\Services\LiberpayService::class);
                $saleData = $liberpayService->findSale($saleId);
                
                if ($saleData) {
                    Log::info('Liberpay: Status verificado na API', [
                        'sale_id' => $saleId,
                        'status' => $saleData['status'] ?? 'unknown',
                    ]);
                    return $saleData;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            $acquirerName = $isFullPix ? 'FullPix' : 'Liberpay';
            Log::error("{$acquirerName}: Erro ao verificar status na API", [
                'sale_id' => $saleId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Retorna mensagem amigável do status
     */
    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'paid' => 'Pagamento confirmado',
            'pending' => 'Aguardando pagamento',
            'expired' => 'Pagamento expirado',
            'cancelled' => 'Pagamento cancelado',
            default => 'Status desconhecido',
        };
    }

    /**
     * Cria uma transação quando o pagamento é confirmado
     */
    private function createTransactionFromSale(\App\Models\LiberpaySale $liberpaySale): void
    {
        $user = $liberpaySale->user;
        
        // Verificar se já existe transação para esta venda
        $existingTransaction = \App\Models\Transaction::where('acquirer_ref', $liberpaySale->liberpay_sale_id)->first();
        
        if ($existingTransaction) {
            return;
        }
        
        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $liberpaySale->amount;
        
        // Buscar adquirente LiberPay para calcular taxas corretas
        $liberpayAcquirer = \App\Models\Acquirer::where('slug', 'liberpay')->first();
        
        // Calcular taxas (LiberPay + LuckPay configurável)
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $liberpaySale->user, $liberpayAcquirer);
        
        $totalFees = $feeCalculation['total_fees'];
        $netDeposit = $feeCalculation['net_amount'];

        // Extrair product_id do metadata se disponível (vindo de checkout)
        $productId = $liberpaySale->metadata['product_id'] ?? $liberpaySale->metadata['checkout_product_id'] ?? null;
        
        // Criar nova transação
        \App\Models\Transaction::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'invoice' => 'LIB-' . $liberpaySale->liberpay_sale_id,
            'payment_status' => 'Paid',
            'total_amount' => $totalAmount,
            'payment_method' => 'PIX',
            'net_deposit' => $netDeposit, // Valor líquido após desconto de todas as taxas
            'acquirer_ref' => $liberpaySale->liberpay_sale_id,
            'date' => today(), // Usar today() para garantir apenas a data sem hora
            'fee' => $feeCalculation['gateway_fee'], // Apenas taxa da gateway (LuckPay), não inclui taxa da LiberPay
            'is_sample' => false,
        ]);
        
        // Atualizar saldo do usuário
        $user->increment('balance', $netDeposit);
        
        \Illuminate\Support\Facades\Log::info('Liberpay: Transação criada via polling', [
            'sale_id' => $liberpaySale->liberpay_sale_id,
            'total_amount' => $totalAmount,
            'total_fees' => $totalFees,
            'net_deposit' => $netDeposit,
            'product_id' => $productId,
        ]);
    }

    /**
     * Cria uma transação quando o pagamento é confirmado (FullPix)
     */
    private function createTransactionFromFullPixSale(\App\Models\FullPixSale $fullpixSale): void
    {
        $user = $fullpixSale->user;
        
        // Verificar se já existe transação para esta venda
        $existingTransaction = \App\Models\Transaction::where('acquirer_ref', $fullpixSale->fullpix_transaction_id)->first();
        
        if ($existingTransaction) {
            return;
        }
        
        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $fullpixSale->amount;
        
        // Buscar adquirente FullPix para calcular taxas corretas
        $fullpixAcquirer = \App\Models\Acquirer::where('slug', 'fullpix')->first();
        
        // Calcular taxas (FullPix + LuckPay configurável)
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $fullpixSale->user, $fullpixAcquirer);
        
        $totalFees = $feeCalculation['total_fees'];
        $netDeposit = $feeCalculation['net_amount'];

        // Extrair product_id do metadata se disponível (vindo de checkout)
        $productId = $fullpixSale->metadata['product_id'] ?? $fullpixSale->metadata['checkout_product_id'] ?? null;
        
        // Criar nova transação
        \App\Models\Transaction::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'invoice' => 'FPX-' . $fullpixSale->fullpix_transaction_id,
            'payment_status' => 'Paid',
            'total_amount' => $totalAmount,
            'payment_method' => 'PIX',
            'net_deposit' => $netDeposit, // Valor líquido após desconto de todas as taxas
            'acquirer_ref' => $fullpixSale->fullpix_transaction_id,
            'date' => today(), // Usar today() para garantir apenas a data sem hora
            'fee' => $feeCalculation['gateway_fee'], // Apenas taxa da gateway (LuckPay), não inclui taxa da FullPix
            'is_sample' => false,
        ]);
        
        // Atualizar saldo do usuário
        $user->increment('balance', $netDeposit);
        
        \Illuminate\Support\Facades\Log::info('FullPix: Transação criada via polling', [
            'transaction_id' => $fullpixSale->fullpix_transaction_id,
            'total_amount' => $totalAmount,
            'total_fees' => $totalFees,
            'net_deposit' => $netDeposit,
            'product_id' => $productId,
        ]);
    }
}
