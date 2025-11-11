<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexTransactionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        // Buscar transações pagas
        $paidTransactions = Transaction::with('user')
            ->where('is_sample', false)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Buscar vendas pendentes do Liberpay que ainda não viraram transação
        $pendingLiberpaySales = \App\Models\LiberpaySale::with('user')
            ->where('status', 'pending')
            ->whereDoesntHave('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Buscar vendas pendentes da FullPix que ainda não viraram transação
        $pendingFullPixSales = \App\Models\FullPixSale::with('user')
            ->whereIn('status', ['waiting_payment', 'pending'])
            ->whereDoesntHave('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Combinar e formatar
        $allTransactions = collect();
        
        // Adicionar transações pagas
        foreach ($paidTransactions as $transaction) {
            // Verificar se é LiberPay ou FullPix
            $liberpaySale = \App\Models\LiberpaySale::where('liberpay_sale_id', $transaction->acquirer_ref)->first();
            $fullpixSale = \App\Models\FullPixSale::where('fullpix_transaction_id', $transaction->acquirer_ref)->first();
            
            $sale = $liberpaySale ?? $fullpixSale;
            $isFullPix = $fullpixSale !== null;
            
            // Determinar produto
            $product = 'Depósito';
            if ($transaction->product_id) {
                $productModel = \App\Models\Seller\Product::find($transaction->product_id);
                $product = $productModel ? $productModel->name : 'Depósito';
            } elseif ($sale) {
                if ($isFullPix) {
                    $product = $fullpixSale->fullpix_response['items'][0]['title'] ?? 
                               ($fullpixSale->fullpix_response['description'] ?? 'Depósito');
                } else {
                    $product = $liberpaySale?->liberpay_response['items'][0]['title'] ?? 'Depósito';
                }
            }
            
            // Verificar se é depósito interno
            $isInternalDeposit = $sale && 
                $sale->external_reference && 
                str_starts_with($sale->external_reference, 'LUCKPAY-');
            
            if ($isInternalDeposit) {
                $product = 'Depósito Interno';
            }
            
            // Cliente: se for checkout, usar email do lead; caso contrário, email do seller
            $clientEmail = $transaction->user->email ?? 'N/A';
            $clientName = $transaction->user->name ?? 'N/A';
            
            // Se for checkout, tentar pegar email do lead
            if ($sale && isset($sale->metadata['form_data']['email'])) {
                $clientEmail = $sale->metadata['form_data']['email'];
                $clientName = $sale->metadata['form_data']['name'] ?? $clientEmail;
            } elseif ($sale && isset($sale->metadata['checkout_id'])) {
                // Fallback: tentar pegar da resposta da API
                if ($isFullPix && isset($fullpixSale->fullpix_response['customer']['email'])) {
                    $clientEmail = $fullpixSale->fullpix_response['customer']['email'];
                    $clientName = $fullpixSale->fullpix_response['customer']['name'] ?? $clientEmail;
                } elseif (!$isFullPix && isset($liberpaySale->liberpay_response['customer']['email'])) {
                    $clientEmail = $liberpaySale->liberpay_response['customer']['email'];
                    $clientName = $liberpaySale->liberpay_response['customer']['name'] ?? $clientEmail;
                }
            }
            
            $allTransactions->push([
                'id' => $transaction->id,
                'transaction_id' => $transaction->acquirer_ref ?? $transaction->invoice,
                'clientName' => $clientName,
                'clientEmail' => $clientEmail,
                'status' => 'concluida',
                'document' => $transaction->user->document ?? 'N/A',
                'type' => 'Produto Digital',
                'method' => $transaction->payment_method ?? 'PIX',
                'acquirer' => $transaction->acquirer_ref ?? 'N/A',
                'sellerEmail' => $transaction->user->email ?? 'N/A',
                'value' => (float) $transaction->total_amount,
                'fee' => (float) $transaction->fee,
                'net_deposit' => (float) $transaction->net_deposit,
                'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'paid_at' => $transaction->date?->format('Y-m-d H:i:s'),
                'expires_at' => $sale?->expires_at?->format('Y-m-d H:i:s'),
                'transactionId' => $transaction->invoice,
                'pixReference' => $transaction->acquirer_ref,
                'liberpay_sale_id' => $isFullPix ? null : $transaction->acquirer_ref,
                'fullpix_transaction_id' => $isFullPix ? $transaction->acquirer_ref : null,
                'external_reference' => $sale?->external_reference,
                'product' => $product,
            ]);
        }
        
        // Adicionar vendas pendentes do LiberPay
        foreach ($pendingLiberpaySales as $sale) {
            $acquirer = \App\Models\Acquirer::where('slug', 'liberpay')->first();
            $gatewayFeePercentage = $acquirer?->gateway_fee_percentage ?? 2.99;
            $fee = ($sale->amount * $gatewayFeePercentage) / 100;
            
            // Determinar produto
            $product = 'Depósito';
            $productId = $sale->metadata['product_id'] ?? $sale->metadata['checkout_product_id'] ?? null;
            if ($productId) {
                $productModel = \App\Models\Seller\Product::find($productId);
                $product = $productModel ? $productModel->name : 'Depósito';
            } else {
                $product = $sale->liberpay_response['items'][0]['title'] ?? 'Depósito';
            }
            
            // Cliente: se for checkout, usar email do lead; caso contrário, email do seller
            $clientEmail = $sale->user->email ?? 'N/A';
            $clientName = $sale->user->name ?? 'N/A';
            
            // Se for checkout, tentar pegar email do lead
            if (isset($sale->metadata['form_data']['email'])) {
                $clientEmail = $sale->metadata['form_data']['email'];
                $clientName = $sale->metadata['form_data']['name'] ?? $clientEmail;
            } elseif (isset($sale->metadata['checkout_id'])) {
                // Fallback: tentar pegar da resposta da API
                if (isset($sale->liberpay_response['customer']['email'])) {
                    $clientEmail = $sale->liberpay_response['customer']['email'];
                    $clientName = $sale->liberpay_response['customer']['name'] ?? $clientEmail;
                }
            }
            
            $allTransactions->push([
                'id' => 'pending-' . $sale->id,
                'transaction_id' => $sale->liberpay_sale_id,
                'clientName' => $clientName,
                'clientEmail' => $clientEmail,
                'status' => 'pendente',
                'document' => $sale->user->document ?? 'N/A',
                'type' => 'Produto Digital',
                'method' => 'PIX',
                'acquirer' => $sale->liberpay_sale_id,
                'sellerEmail' => $sale->user->email ?? 'N/A',
                'value' => (float) $sale->amount,
                'fee' => (float) $fee,
                'net_deposit' => (float) ($sale->amount - $fee),
                'date' => $sale->created_at->format('Y-m-d H:i:s'),
                'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                'paid_at' => null,
                'transactionId' => 'LIB-' . $sale->liberpay_sale_id,
                'pixReference' => $sale->liberpay_sale_id,
                'liberpay_sale_id' => $sale->liberpay_sale_id,
                'external_reference' => $sale->external_reference,
                'product' => $product,
                'expires_at' => $sale->expires_at?->format('Y-m-d H:i:s'),
            ]);
        }
        
        // Adicionar vendas pendentes da FullPix
        foreach ($pendingFullPixSales as $sale) {
            // Usar FeeCalculationService para calcular taxas corretamente
            $feeService = new \App\Services\FeeCalculationService();
            $fullpixAcquirer = \App\Models\Acquirer::where('slug', 'fullpix')->first();
            $feeCalculation = $feeService->calculateNetAmount((float) $sale->amount, $sale->user, $fullpixAcquirer);
            $fee = $feeCalculation['total_fees'];
            
            // Determinar produto
            $product = 'Depósito';
            $productId = $sale->metadata['product_id'] ?? $sale->metadata['checkout_product_id'] ?? null;
            if ($productId) {
                $productModel = \App\Models\Seller\Product::find($productId);
                $product = $productModel ? $productModel->name : 'Depósito';
            } else {
                $product = $sale->fullpix_response['items'][0]['title'] ?? 
                           ($sale->fullpix_response['description'] ?? 'Depósito');
            }
            
            // Verificar se é depósito interno
            $isInternalDeposit = $sale->external_reference && 
                str_starts_with($sale->external_reference, 'LUCKPAY-');
            
            if ($isInternalDeposit) {
                $product = 'Depósito Interno';
            }
            
            // Cliente: se for checkout, usar email do lead; caso contrário, email do seller
            $clientEmail = $sale->user->email ?? 'N/A';
            $clientName = $sale->user->name ?? 'N/A';
            
            // Se for checkout, tentar pegar email do lead
            if (isset($sale->metadata['form_data']['email'])) {
                $clientEmail = $sale->metadata['form_data']['email'];
                $clientName = $sale->metadata['form_data']['name'] ?? $clientEmail;
            } elseif (isset($sale->metadata['checkout_id'])) {
                // Fallback: tentar pegar da resposta da API
                if (isset($sale->fullpix_response['customer']['email'])) {
                    $clientEmail = $sale->fullpix_response['customer']['email'];
                    $clientName = $sale->fullpix_response['customer']['name'] ?? $clientEmail;
                }
            }
            
            $allTransactions->push([
                'id' => 'pending-fpx-' . $sale->id,
                'transaction_id' => $sale->fullpix_transaction_id,
                'clientName' => $clientName,
                'clientEmail' => $clientEmail,
                'status' => 'pendente',
                'document' => $sale->user->document ?? 'N/A',
                'type' => 'Produto Digital',
                'method' => 'PIX',
                'acquirer' => $sale->fullpix_transaction_id,
                'sellerEmail' => $sale->user->email ?? 'N/A',
                'value' => (float) $sale->amount,
                'fee' => (float) $fee,
                'net_deposit' => (float) ($sale->amount - $fee),
                'date' => $sale->created_at->format('Y-m-d H:i:s'),
                'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                'paid_at' => null,
                'transactionId' => 'FPX-' . $sale->fullpix_transaction_id,
                'pixReference' => $sale->fullpix_transaction_id,
                'liberpay_sale_id' => null,
                'fullpix_transaction_id' => $sale->fullpix_transaction_id,
                'external_reference' => $sale->external_reference,
                'product' => $product,
                'expires_at' => $sale->expires_at?->format('Y-m-d H:i:s'),
            ]);
        }
        
        // Ordenar por data e paginar manualmente
        $sorted = $allTransactions->sortByDesc('date')->values();
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $paginated,
        ]);
    }

    private function mapStatus(string $status): string
    {
        return match($status) {
            'Paid' => 'concluida',
            'Pending' => 'pendente',
            'Unpaid' => 'cancelada',
            default => 'pendente',
        };
    }
}
