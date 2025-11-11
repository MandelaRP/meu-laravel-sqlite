<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Transactions;

use App\Http\Controllers\Controller;
use App\Models\FullPixSale;
use App\Models\LiberpaySale;
use App\Models\Transaction;
use App\Services\FeeCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowTransactionController extends Controller
{
    public function __invoke(Request $request, string $id): Response
    {
        // Tentar buscar como Transaction primeiro
        $transaction = Transaction::with('user')->find($id);
        
        if ($transaction) {
            // Verificar se é LiberPay ou FullPix
            $liberpaySale = LiberpaySale::where('liberpay_sale_id', $transaction->acquirer_ref)->first();
            $fullpixSale = FullPixSale::where('fullpix_transaction_id', $transaction->acquirer_ref)->first();
            $data = $this->formatTransactionData($transaction, $liberpaySale, $fullpixSale);
        } else {
            // Se não for Transaction, buscar como LiberpaySale pendente
            $liberpaySale = LiberpaySale::with('user')->find($id);
            
            if (!$liberpaySale) {
                // Tentar buscar pelo transaction_id (liberpay_sale_id)
                $liberpaySale = LiberpaySale::where('liberpay_sale_id', $id)->with('user')->first();
            }
            
            if ($liberpaySale) {
                $data = $this->formatLiberpaySaleData($liberpaySale);
            } else {
                // Tentar buscar como FullPixSale pendente
                $fullpixSale = FullPixSale::with('user')->find($id);
                
                if (!$fullpixSale) {
                    // Tentar buscar pelo transaction_id (fullpix_transaction_id)
                    $fullpixSale = FullPixSale::where('fullpix_transaction_id', $id)->with('user')->first();
                }
                
                // Também tentar buscar pelo ID que pode vir como "pending-fpx-{id}"
                if (!$fullpixSale && str_starts_with($id, 'pending-fpx-')) {
                    $saleId = str_replace('pending-fpx-', '', $id);
                    $fullpixSale = FullPixSale::with('user')->find($saleId);
                }
                
                if (!$fullpixSale) {
                    abort(404, 'Transação não encontrada');
                }
                
                $data = $this->formatFullPixSaleData($fullpixSale);
            }
        }

        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $data,
        ]);
    }

    private function formatTransactionData(Transaction $transaction, ?LiberpaySale $liberpaySale, ?FullPixSale $fullpixSale): array
    {
        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $transaction->total_amount;
        
        // Determinar qual adquirente usar para cálculo de taxas
        $acquirer = null;
        if ($fullpixSale) {
            $acquirer = \App\Models\Acquirer::where('slug', 'fullpix')->first();
        } else {
            $acquirer = \App\Models\Acquirer::where('slug', 'liberpay')->first();
        }
        
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $transaction->user, $acquirer);
        
        // Extrair dados do cliente do checkout se disponível
        $customerData = $this->extractCustomerData($liberpaySale, $fullpixSale, $transaction);
        
        $sale = $fullpixSale ?? $liberpaySale;
        
        return [
            'id' => $transaction->id,
            'transaction_id' => $transaction->acquirer_ref ?? $transaction->invoice,
            'status' => $this->mapStatus($transaction->payment_status),
            'raw_status' => $transaction->payment_status,
            'total_amount' => $totalAmount,
            'fee' => (float) $feeCalculation['total_fees'], // Total de taxas (Adquirente + LuckPay)
            'net_deposit' => (float) $feeCalculation['net_amount'],
            'payment_method' => $transaction->payment_method ?? 'PIX',
            'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $transaction->updated_at->format('Y-m-d H:i:s'),
            'paid_at' => $transaction->date?->format('Y-m-d H:i:s'),
            'refunded_at' => null, // TODO: adicionar quando houver reembolso
            'liberpay_sale_id' => $fullpixSale ? null : $transaction->acquirer_ref,
            'fullpix_transaction_id' => $fullpixSale ? $transaction->acquirer_ref : null,
            'external_reference' => $sale?->external_reference ?? 'Não informado',
            'product' => $this->getProductName($liberpaySale, $fullpixSale),
            'customer' => $customerData,
        ];
    }

    private function formatLiberpaySaleData(LiberpaySale $liberpaySale): array
    {
        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $liberpaySale->amount;
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $liberpaySale->user);
        
        // Extrair dados do cliente do checkout se disponível
        $customerData = $this->extractCustomerData($liberpaySale, null, null);
        
        return [
            'id' => 'pending-' . $liberpaySale->id,
            'transaction_id' => $liberpaySale->liberpay_sale_id,
            'status' => $this->mapStatus($liberpaySale->status),
            'raw_status' => $liberpaySale->status,
            'total_amount' => $totalAmount,
            'fee' => (float) $feeCalculation['total_fees'], // Total de taxas (LiberPay + LuckPay)
            'net_deposit' => (float) $feeCalculation['net_amount'],
            'payment_method' => 'PIX',
            'created_at' => $liberpaySale->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $liberpaySale->updated_at->format('Y-m-d H:i:s'),
            'paid_at' => $liberpaySale->paid_at?->format('Y-m-d H:i:s'),
            'refunded_at' => null,
            'liberpay_sale_id' => $liberpaySale->liberpay_sale_id,
            'external_reference' => $liberpaySale->external_reference ?? 'Não informado',
            'expires_at' => $liberpaySale->expires_at?->format('Y-m-d H:i:s'),
            'product' => $this->getProductName($liberpaySale, null),
            'customer' => $customerData,
        ];
    }
    
    /**
     * Extrai dados do cliente do checkout ou usa dados do usuário
     */
    private function extractCustomerData(?LiberpaySale $liberpaySale, ?FullPixSale $fullpixSale, ?Transaction $transaction): array
    {
        $user = $liberpaySale?->user ?? $fullpixSale?->user ?? $transaction?->user;
        
        if (!$user) {
            return [
                'name' => 'N/A',
                'email' => 'N/A',
                'document' => 'Não informado',
                'phone' => 'Não informado',
                'address' => 'Não informado',
                'neighborhood' => 'Não informado',
                'city_state' => 'Não informado',
                'zip_code' => 'Não informado',
            ];
        }
        
        // Se for via checkout, tentar extrair dados do checkout
        $sale = $fullpixSale ?? $liberpaySale;
        $metadata = $sale?->metadata ?? [];
        $isFromCheckout = isset($metadata['product_id']) && $metadata['product_id'] !== null;
        
        if ($isFromCheckout && $sale) {
            // Prioridade: form_data do metadata (dados preenchidos no checkout)
            $formData = $metadata['form_data'] ?? null;
            
            if ($formData && is_array($formData)) {
                // Usar dados do formulário preenchido no checkout
                $cityState = '';
                if (!empty($formData['city']) && !empty($formData['state'])) {
                    $cityState = $formData['city'] . '/' . $formData['state'];
                } elseif (!empty($formData['city'])) {
                    $cityState = $formData['city'];
                } elseif (!empty($formData['state'])) {
                    $cityState = $formData['state'];
                } else {
                    $cityState = 'Não informado';
                }
                
                return [
                    'name' => $formData['name'] ?? 'N/A',
                    'email' => $formData['email'] ?? 'N/A',
                    'document' => $this->formatDocument($formData['cpf'] ?? null),
                    'phone' => $formData['phone'] ?? 'Não informado',
                    'address' => $formData['address'] ?? 'Não informado',
                    'neighborhood' => $formData['complement'] ?? 'Não informado', // Usar complement como bairro se não houver
                    'city_state' => $cityState,
                    'zip_code' => $formData['zip_code'] ?? 'Não informado',
                ];
            }
            
            // Fallback: dados da API
            // FullPix usa estrutura diferente
            if ($fullpixSale && isset($fullpixSale->fullpix_response['customer'])) {
                $customer = $fullpixSale->fullpix_response['customer'];
                $document = $customer['document'] ?? null;
                $shipping = $fullpixSale->fullpix_response['shipping'] ?? null;
                
                return [
                    'name' => $customer['name'] ?? $user->name ?? 'N/A',
                    'email' => $customer['email'] ?? $user->email ?? 'N/A',
                    'document' => $this->formatDocument($document),
                    'phone' => $customer['phone'] ?? $user->phone ?? 'Não informado',
                    'address' => $shipping['street'] ?? $customer['address'] ?? 'Não informado',
                    'neighborhood' => $shipping['neighborhood'] ?? $customer['neighborhood'] ?? 'Não informado',
                    'city_state' => $this->formatCityState($shipping ?? $customer),
                    'zip_code' => $shipping['zipCode'] ?? $customer['zip_code'] ?? 'Não informado',
                ];
            }
            
            // LiberPay
            if ($liberpaySale && isset($liberpaySale->liberpay_response['customer'])) {
                $customer = $liberpaySale->liberpay_response['customer'];
                $document = $customer['document'] ?? null;
                
                return [
                    'name' => $customer['name'] ?? $user->name ?? 'N/A',
                    'email' => $customer['email'] ?? $user->email ?? 'N/A',
                    'document' => $this->formatDocument($document),
                    'phone' => $customer['phone'] ?? $user->phone ?? 'Não informado',
                    'address' => $customer['address'] ?? $customer['street'] ?? 'Não informado',
                    'neighborhood' => $customer['neighborhood'] ?? $customer['district'] ?? 'Não informado',
                    'city_state' => $this->formatCityState($customer),
                    'zip_code' => $customer['zip_code'] ?? $customer['postal_code'] ?? 'Não informado',
                ];
            }
        }
        
        // Se for depósito interno, usar dados básicos do usuário
        return [
            'name' => $user->name ?? 'N/A',
            'email' => $user->email ?? 'N/A',
            'document' => $this->formatDocument($user->document),
            'phone' => $user->phone ?? 'Não informado',
            'address' => 'Não informado',
            'neighborhood' => 'Não informado',
            'city_state' => 'Não informado',
            'zip_code' => 'Não informado',
        ];
    }
    
    /**
     * Formata documento (CPF/CNPJ)
     */
    private function formatDocument(?string $document): string
    {
        if (!$document || $document === 'Não informado') {
            return 'Não informado';
        }
        
        // Se for objeto (da API), extrair number
        if (is_array($document)) {
            $document = $document['number'] ?? null;
        }
        
        if (!$document) {
            return 'Não informado';
        }
        
        $cleaned = preg_replace('/[^0-9]/', '', $document);
        
        // CPF (11 dígitos)
        if (strlen($cleaned) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cleaned);
        }
        
        // CNPJ (14 dígitos)
        if (strlen($cleaned) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cleaned);
        }
        
        return $document;
    }
    
    /**
     * Formata cidade/UF
     */
    private function formatCityState(array $customer): string
    {
        $city = $customer['city'] ?? null;
        $state = $customer['state'] ?? $customer['uf'] ?? null;
        
        if (!$city && !$state) {
            return 'Não informado';
        }
        
        return trim(($city ?? '') . ($state ? '/' . $state : ''));
    }
    
    /**
     * Obtém nome do produto
     */
    private function getProductName(?LiberpaySale $liberpaySale, ?FullPixSale $fullpixSale): string
    {
        $sale = $fullpixSale ?? $liberpaySale;
        
        if (!$sale) {
            return 'Depósito Interno';
        }
        
        // Verificar se é depósito interno (sem product_id no metadata)
        $metadata = $sale->metadata ?? [];
        $isDeposit = !isset($metadata['product_id']) && !isset($metadata['checkout_product_id']);
        
        if ($isDeposit) {
            return 'Depósito Interno';
        }
        
        // Se for checkout, pegar nome do produto da resposta da API
        if ($fullpixSale && isset($fullpixSale->fullpix_response['items'][0]['title'])) {
            return $fullpixSale->fullpix_response['items'][0]['title'];
        }
        
        if ($liberpaySale && isset($liberpaySale->liberpay_response['items'][0]['title'])) {
            return $liberpaySale->liberpay_response['items'][0]['title'];
        }
        
        return 'Produto';
    }
    
    /**
     * Formata dados de uma venda FullPix pendente
     */
    private function formatFullPixSaleData(FullPixSale $fullpixSale): array
    {
        // Usar FeeCalculationService para calcular taxas corretamente
        $feeService = new FeeCalculationService();
        $totalAmount = (float) $fullpixSale->amount;
        
        // Buscar adquirente FullPix para calcular taxas corretas
        $fullpixAcquirer = \App\Models\Acquirer::where('slug', 'fullpix')->first();
        $feeCalculation = $feeService->calculateNetAmount($totalAmount, $fullpixSale->user, $fullpixAcquirer);
        
        // Extrair dados do cliente do checkout se disponível
        $customerData = $this->extractCustomerData(null, $fullpixSale, null);
        
        return [
            'id' => 'pending-fpx-' . $fullpixSale->id,
            'transaction_id' => $fullpixSale->fullpix_transaction_id,
            'status' => $this->mapStatus($fullpixSale->status),
            'raw_status' => $fullpixSale->status,
            'total_amount' => $totalAmount,
            'fee' => (float) $feeCalculation['total_fees'], // Total de taxas (FullPix + LuckPay)
            'net_deposit' => (float) $feeCalculation['net_amount'],
            'payment_method' => 'PIX',
            'created_at' => $fullpixSale->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $fullpixSale->updated_at->format('Y-m-d H:i:s'),
            'paid_at' => $fullpixSale->paid_at?->format('Y-m-d H:i:s'),
            'refunded_at' => null,
            'liberpay_sale_id' => null,
            'fullpix_transaction_id' => $fullpixSale->fullpix_transaction_id,
            'external_reference' => $fullpixSale->external_reference ?? 'Não informado',
            'expires_at' => $fullpixSale->expires_at?->format('Y-m-d H:i:s'),
            'product' => $this->getProductName(null, $fullpixSale),
            'customer' => $customerData,
        ];
    }

    private function mapStatus(string $status): string
    {
        return match(strtolower($status)) {
            'paid', 'pago', 'approved' => 'pago',
            'pending', 'pendente' => 'pendente',
            'unpaid', 'cancelado', 'cancelled' => 'cancelado',
            'expired', 'expirado' => 'expirado',
            'refunded', 'reembolsado' => 'reembolsado',
            default => 'pendente',
        };
    }
}

