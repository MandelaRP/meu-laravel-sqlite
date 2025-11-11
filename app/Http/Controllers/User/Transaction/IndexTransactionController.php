<?php

declare(strict_types = 1);

namespace App\Http\Controllers\User\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexTransactionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login');
            }
            
            // Buscar transações pagas
            $query = Transaction::where('user_id', $user->id);
            
            if (\Illuminate\Support\Facades\Schema::hasColumn('transactions', 'is_sample')) {
                $query->where('is_sample', false);
            }
            
            $paidTransactions = $query->orderBy('created_at', 'desc')->get();
            
            // Buscar também vendas pendentes do Liberpay que ainda não viraram transação
            $pendingLiberpaySales = \App\Models\LiberpaySale::where('user_id', $user->id)
                ->where('status', 'pending')
                ->whereDoesntHave('transaction')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Buscar vendas pendentes da FullPix que ainda não viraram transação
            $pendingFullPixSales = \App\Models\FullPixSale::where('user_id', $user->id)
                ->whereIn('status', ['waiting_payment', 'pending'])
                ->whereDoesntHave('transaction')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Combinar transações pagas com vendas pendentes
            $allTransactions = collect();
            
            // Adicionar transações pagas
            foreach ($paidTransactions as $transaction) {
                // Verificar se é LiberPay ou FullPix
                $liberpaySale = \App\Models\LiberpaySale::where('liberpay_sale_id', $transaction->acquirer_ref)->first();
                $fullpixSale = \App\Models\FullPixSale::where('fullpix_transaction_id', $transaction->acquirer_ref)->first();
                
                $sale = $liberpaySale ?? $fullpixSale;
                $isFullPix = $fullpixSale !== null;
                
                // Verificar se é depósito interno (external_reference começa com LUCKPAY)
                $isInternalDeposit = $sale && 
                    $sale->external_reference && 
                    str_starts_with($sale->external_reference, 'LUCKPAY-');
                
                // Buscar nome do produto se product_id estiver disponível
                $product = 'Depósito Interno';
                if (!$isInternalDeposit && $transaction->product_id) {
                    $productModel = \App\Models\Seller\Product::find($transaction->product_id);
                    $product = $productModel ? $productModel->name : 'Depósito';
                } elseif (!$isInternalDeposit && $sale) {
                    // Fallback para nome do item na resposta da API
                    if ($isFullPix) {
                        $product = $fullpixSale->fullpix_response['items'][0]['title'] ?? 
                                   ($fullpixSale->fullpix_response['description'] ?? 'Depósito');
                    } else {
                        $product = $liberpaySale?->liberpay_response['items'][0]['title'] ?? 'Depósito';
                    }
                }
                
                // Cliente: se for checkout, usar email do lead; caso contrário, email do seller
                $customerEmail = $user->email;
                $customerName = $user->name ?? $user->email;
                
                // Se for checkout, tentar pegar email do lead
                if ($sale && isset($sale->metadata['form_data']['email'])) {
                    $customerEmail = $sale->metadata['form_data']['email'];
                    $customerName = $sale->metadata['form_data']['name'] ?? $customerEmail;
                } elseif ($sale && isset($sale->metadata['checkout_id'])) {
                    // Fallback: tentar pegar da resposta da API
                    if ($isFullPix && isset($fullpixSale->fullpix_response['customer']['email'])) {
                        $customerEmail = $fullpixSale->fullpix_response['customer']['email'];
                        $customerName = $fullpixSale->fullpix_response['customer']['name'] ?? $customerEmail;
                    } elseif (!$isFullPix && isset($liberpaySale->liberpay_response['customer']['email'])) {
                        $customerEmail = $liberpaySale->liberpay_response['customer']['email'];
                        $customerName = $liberpaySale->liberpay_response['customer']['name'] ?? $customerEmail;
                    }
                }
                
                $allTransactions->push([
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->acquirer_ref ?? $transaction->invoice,
                    'invoice' => $transaction->invoice ?? 'N/A',
                    'payment_method' => $this->formatPaymentMethod($transaction->payment_method ?? 'PIX'),
                    'payment_status' => 'Pago',
                    'raw_status' => 'paid',
                    'total_amount' => (float) ($transaction->total_amount ?? 0),
                    'fee' => (float) ($transaction->fee ?? 0),
                    'net_deposit' => (float) ($transaction->net_deposit ?? 0),
                    'product' => $product,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'date' => $transaction->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at' => $transaction->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'paid_at' => $transaction->date?->format('Y-m-d H:i:s'),
                    'seller_email' => $user->email,
                    'liberpay_sale_id' => $isFullPix ? null : $transaction->acquirer_ref,
                    'fullpix_transaction_id' => $isFullPix ? $transaction->acquirer_ref : null,
                    'external_reference' => $sale?->external_reference,
                    'expires_at' => $sale?->expires_at?->format('Y-m-d H:i:s'),
                ]);
            }
            
            // Adicionar vendas pendentes do LiberPay
            foreach ($pendingLiberpaySales as $sale) {
                $acquirer = \App\Models\Acquirer::where('slug', 'liberpay')->first();
                $gatewayFeePercentage = $acquirer?->gateway_fee_percentage ?? 2.99;
                $fee = ($sale->amount * $gatewayFeePercentage) / 100;
                
                // Verificar se é depósito interno
                $isInternalDeposit = $sale->external_reference && 
                    str_starts_with($sale->external_reference, 'LUCKPAY-');
                
                // Buscar nome do produto se product_id estiver disponível no metadata
                $product = 'Depósito Interno';
                if (!$isInternalDeposit) {
                    $productId = $sale->metadata['product_id'] ?? $sale->metadata['checkout_product_id'] ?? null;
                    if ($productId) {
                        $productModel = \App\Models\Seller\Product::find($productId);
                        $product = $productModel ? $productModel->name : 'Depósito';
                    } else {
                        // Fallback para nome do item na resposta da API
                        $product = $sale->liberpay_response['items'][0]['title'] ?? 'Depósito';
                    }
                }
                
                // Cliente: se for checkout, usar email do lead; caso contrário, email do seller
                $customerEmail = $user->email;
                $customerName = $user->name ?? $user->email;
                
                // Se for checkout, tentar pegar email do lead
                if (isset($sale->metadata['form_data']['email'])) {
                    $customerEmail = $sale->metadata['form_data']['email'];
                    $customerName = $sale->metadata['form_data']['name'] ?? $customerEmail;
                } elseif (isset($sale->metadata['checkout_id'])) {
                    // Fallback: tentar pegar da resposta da API
                    if (isset($sale->liberpay_response['customer']['email'])) {
                        $customerEmail = $sale->liberpay_response['customer']['email'];
                        $customerName = $sale->liberpay_response['customer']['name'] ?? $customerEmail;
                    }
                }
                
                $allTransactions->push([
                    'id' => 'pending-' . $sale->id,
                    'transaction_id' => $sale->liberpay_sale_id,
                    'invoice' => 'LIB-' . $sale->liberpay_sale_id,
                    'payment_method' => 'PIX',
                    'payment_status' => 'Pendente',
                    'raw_status' => 'pending',
                    'total_amount' => (float) $sale->amount,
                    'fee' => (float) $fee,
                    'net_deposit' => (float) ($sale->amount - $fee),
                    'product' => $product,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'date' => $sale->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at' => $sale->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'paid_at' => null,
                    'seller_email' => $user->email,
                    'liberpay_sale_id' => $sale->liberpay_sale_id,
                    'external_reference' => $sale->external_reference,
                    'expires_at' => $sale->expires_at?->format('Y-m-d H:i:s'),
                ]);
            }
            
            // Adicionar vendas pendentes da FullPix
            foreach ($pendingFullPixSales as $sale) {
                // Usar FeeCalculationService para calcular taxas corretamente
                $feeService = new \App\Services\FeeCalculationService();
                $fullpixAcquirer = \App\Models\Acquirer::where('slug', 'fullpix')->first();
                $feeCalculation = $feeService->calculateNetAmount((float) $sale->amount, $user, $fullpixAcquirer);
                $fee = $feeCalculation['total_fees'];
                
                // Verificar se é depósito interno
                $isInternalDeposit = $sale->external_reference && 
                    str_starts_with($sale->external_reference, 'LUCKPAY-');
                
                // Buscar nome do produto se product_id estiver disponível no metadata
                $product = 'Depósito Interno';
                if (!$isInternalDeposit) {
                    $productId = $sale->metadata['product_id'] ?? $sale->metadata['checkout_product_id'] ?? null;
                    if ($productId) {
                        $productModel = \App\Models\Seller\Product::find($productId);
                        $product = $productModel ? $productModel->name : 'Depósito';
                    } else {
                        // Fallback para nome do item na resposta da API
                        $product = $sale->fullpix_response['items'][0]['title'] ?? 
                                   ($sale->fullpix_response['description'] ?? 'Depósito');
                    }
                }
                
                // Cliente: se for checkout, usar email do lead; caso contrário, email do seller
                $customerEmail = $user->email;
                $customerName = $user->name ?? $user->email;
                
                // Se for checkout, tentar pegar email do lead
                if (isset($sale->metadata['form_data']['email'])) {
                    $customerEmail = $sale->metadata['form_data']['email'];
                    $customerName = $sale->metadata['form_data']['name'] ?? $customerEmail;
                } elseif (isset($sale->metadata['checkout_id'])) {
                    // Fallback: tentar pegar da resposta da API
                    if (isset($sale->liberpay_response['customer']['email'])) {
                        $customerEmail = $sale->liberpay_response['customer']['email'];
                        $customerName = $sale->liberpay_response['customer']['name'] ?? $customerEmail;
                    }
                }
                
                $allTransactions->push([
                    'id' => 'pending-fpx-' . $sale->id,
                    'transaction_id' => $sale->fullpix_transaction_id,
                    'invoice' => 'FPX-' . $sale->fullpix_transaction_id,
                    'payment_method' => 'PIX',
                    'payment_status' => 'Pendente',
                    'raw_status' => 'pending',
                    'total_amount' => (float) $sale->amount,
                    'fee' => (float) $fee,
                    'net_deposit' => (float) ($sale->amount - $fee),
                    'product' => $product,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'date' => $sale->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at' => $sale->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'paid_at' => null,
                    'seller_email' => $user->email,
                    'liberpay_sale_id' => null,
                    'fullpix_transaction_id' => $sale->fullpix_transaction_id,
                    'external_reference' => $sale->external_reference,
                    'expires_at' => $sale->expires_at?->format('Y-m-d H:i:s'),
                ]);
            }
            
            // Ordenar por data de criação (mais recente primeiro)
            $sorted = $allTransactions->sortByDesc('date')->values();
            
            // Paginar transações - garantir que sempre retorne estrutura completa
            $page = (int) $request->get('page', 1);
            $perPage = 10;
            $total = $sorted->count();
            
            // Garantir que apenas 10 itens sejam retornados por página
            $items = $sorted->slice(($page - 1) * $perPage, $perPage)->values();
            
            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                    'pageName' => 'page'
                ]
            );
            
            // Garantir que os links de paginação sejam gerados corretamente
            $paginated->withPath($request->url());
            
            // Forçar geração dos links
            $paginated->appends($request->query());

            return Inertia::render('User/Transaction/Index', [
                'transactions' => $paginated,
            ]);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar transações: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            
            // Retornar paginação vazia em caso de erro
            $emptyPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            return Inertia::render('User/Transaction/Index', [
                'transactions' => $emptyPaginated,
                'error' => 'Erro ao carregar transações. Por favor, tente novamente.',
            ]);
        }
    }
    
    private function formatPaymentMethod(?string $method): string
    {
        if (!$method) {
            return 'N/A';
        }
        
        $methodLower = strtolower($method);
        
        if (stripos($methodLower, 'pix') !== false) {
            return 'PIX';
        } elseif (stripos($methodLower, 'cartão') !== false || stripos($methodLower, 'card') !== false || stripos($methodLower, 'credito') !== false || stripos($methodLower, 'crédito') !== false) {
            return 'Cartão de Crédito';
        } elseif (stripos($methodLower, 'boleto') !== false) {
            return 'Boleto';
        } elseif (stripos($methodLower, 'ted') !== false) {
            return 'TED';
        }
        
        return ucfirst($method);
    }
}
