<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Acquirer;
use App\Models\LiberpaySale;
use App\Models\FullPixSale;
use App\Models\Seller\Product;
use App\Services\LiberpayService;
use App\Services\FullPixService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProcessPaymentController extends Controller
{
    public function __construct(
        private LiberpayService $liberpayService,
        private FullPixService $fullPixService
    ) {}

    public function __invoke(Request $request, string $id): Response|RedirectResponse
    {
        try {
            // Buscar checkout com relacionamentos carregados
            $checkout = Checkout::with(['product', 'orderBumps.product'])->where('id', $id)->first();
            
            if (!$checkout) {
                Log::error('Checkout: Checkout não encontrado', [
                    'checkout_id' => $id,
                ]);
                return back()->withErrors(['error' => 'Checkout não encontrado.']);
            }
            
            // Log para debug
            Log::info('Checkout: Dados do checkout', [
                'checkout_id' => $checkout->id,
                'product_id' => $checkout->product_id,
                'product_loaded' => $checkout->relationLoaded('product'),
                'product_exists' => $checkout->product !== null,
            ]);
            
            // Se o relacionamento não foi carregado ou o produto não existe, tentar carregar novamente
            if (!$checkout->product) {
                // Tentar recarregar o relacionamento
                $checkout->load('product');
                
                // Se ainda não existir, verificar se o product_id está definido
                if (!$checkout->product && $checkout->product_id) {
                    $product = Product::find($checkout->product_id);
                    if ($product) {
                        $checkout->setRelation('product', $product);
                    }
                }
            }
            
            if (!$checkout->product) {
                Log::error('Checkout: Produto não encontrado', [
                    'checkout_id' => $checkout->id,
                    'product_id' => $checkout->product_id,
                    'product_exists_in_db' => $checkout->product_id ? Product::where('id', $checkout->product_id)->exists() : false,
                ]);
                return back()->withErrors(['error' => 'Produto não encontrado.']);
            }
            
            // Validar campos obrigatórios do formulário baseado na configuração do checkout
            $formFieldsConfig = $checkout->form_fields_config ?? [];
            $validationRules = [];
            $formData = [];
            
            // Construir regras de validação baseado nos campos configurados como obrigatórios
            foreach ($formFieldsConfig as $key => $fieldConfig) {
                if (isset($fieldConfig['required']) && $fieldConfig['required'] === true) {
                    $validationRules[$key] = 'required|string|max:255';
                    // Adicionar aliases
                    if ($key === 'phone') {
                        $validationRules['telefone'] = 'nullable|string|max:20';
                    } elseif ($key === 'zip_code') {
                        $validationRules['cep'] = 'nullable|string|max:10';
                    } elseif ($key === 'address') {
                        $validationRules['endereco'] = 'nullable|string|max:255';
                    } elseif ($key === 'city') {
                        $validationRules['cidade'] = 'nullable|string|max:255';
                    } elseif ($key === 'state') {
                        $validationRules['estado'] = 'nullable|string|max:2';
                    } elseif ($key === 'number') {
                        $validationRules['numero'] = 'nullable|string|max:10';
                    } elseif ($key === 'complement') {
                        $validationRules['complemento'] = 'nullable|string|max:255';
                    }
                    
                    // Validação específica para email
                    if ($key === 'email' || str_contains($key, 'email')) {
                        $validationRules[$key] = 'required|email|max:255';
                    }
                } else {
                    $validationRules[$key] = 'nullable|string|max:255';
                }
            }
            
            // Validar dados do formulário
            $validatedData = $request->validate($validationRules);
            
            // Normalizar nomes dos campos (usar nomes padrão)
            $normalizedFormData = [
                'name' => $validatedData['name'] ?? '',
                'email' => $validatedData['email'] ?? '',
                'phone' => $validatedData['phone'] ?? $validatedData['telefone'] ?? null,
                'cpf' => $validatedData['cpf'] ?? null,
                'zip_code' => $validatedData['zip_code'] ?? $validatedData['cep'] ?? null,
                'address' => $validatedData['address'] ?? $validatedData['endereco'] ?? null,
                'city' => $validatedData['city'] ?? $validatedData['cidade'] ?? null,
                'state' => $validatedData['state'] ?? $validatedData['estado'] ?? null,
                'number' => $validatedData['number'] ?? $validatedData['numero'] ?? null,
                'complement' => $validatedData['complement'] ?? $validatedData['complemento'] ?? null,
            ];
            
            $formData = $normalizedFormData;

            $paymentMethod = $request->input('payment_method', 'pix');
            $orderBumpIds = $request->input('order_bump_ids', []);

            // Calcular valor total (igual ao frontend)
            // 1. Calcular subtotal (produto + order bumps)
            $productPrice = (float) $checkout->product->price;
            $orderBumpsTotal = 0;
            
            if (!empty($orderBumpIds) && $checkout->orderBumps) {
                foreach ($checkout->orderBumps as $orderBump) {
                    // orderBumpIds pode conter tanto IDs de order bumps quanto product_ids
                    // Verificar ambos para compatibilidade
                    if (in_array($orderBump->id, $orderBumpIds) || in_array((string) $orderBump->id, $orderBumpIds) 
                        || in_array($orderBump->product_id, $orderBumpIds) || in_array((string) $orderBump->product_id, $orderBumpIds)) {
                        $orderBumpsTotal += (float) $orderBump->product->price;
                    }
                }
            }
            
            $subtotal = $productPrice + $orderBumpsTotal;
            
            // 2. Aplicar desconto no subtotal (não apenas no produto)
            $discountPercentage = (float) ($checkout->discount_percentage ?? 0);
            $discountAmount = $subtotal * ($discountPercentage / 100);
            
            // 3. Calcular total final
            $totalAmount = $subtotal - $discountAmount;
            
            // Garantir que o valor seja positivo e tenha no máximo 2 casas decimais
            $totalAmount = max(0, round($totalAmount, 2));
            
            Log::info('Checkout: Cálculo do valor', [
                'checkout_id' => $checkout->id,
                'product_price' => $productPrice,
                'order_bumps_total' => $orderBumpsTotal,
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);

            // Buscar adquirente ativa globalmente
            $acquirer = Acquirer::where('is_active', true)->first();
            
            if (!$acquirer) {
                Log::error('Checkout: Nenhuma adquirente ativa configurada', [
                    'checkout_id' => $checkout->id,
                ]);
                return back()->withErrors(['error' => 'Sistema temporariamente indisponível. Tente novamente em alguns instantes.']);
            }

            $acquirerSlug = $acquirer->slug;

            // Verificar se a adquirente está configurada
            if ($acquirerSlug === 'liberpay' && !$this->liberpayService->isConfigured()) {
                Log::error('Checkout: API da Liberpay não está configurada', [
                    'checkout_id' => $checkout->id,
                ]);
                return back()->withErrors(['error' => 'Sistema temporariamente indisponível. Tente novamente em alguns instantes.']);
            }

            if ($acquirerSlug === 'fullpix' && !$this->fullPixService->isConfigured()) {
                Log::error('Checkout: API da FullPix não está configurada', [
                    'checkout_id' => $checkout->id,
                ]);
                return back()->withErrors(['error' => 'Sistema temporariamente indisponível. Tente novamente em alguns instantes.']);
            }

            // Criar objeto user temporário com dados do formulário para os serviços
            $tempUser = (object) [
                'id' => null,
                'name' => $formData['name'],
                'email' => $formData['email'],
                'phone' => $formData['phone'] ?? null,
                'document' => $formData['cpf'] ?? null,
            ];

            // Preparar metadados
            $metadata = [
                'checkout_id' => $checkout->id,
                'product_id' => $checkout->product_id,
                'checkout_product_id' => $checkout->product_id,
                'order_bump_ids' => $orderBumpIds,
                'form_data' => $formData,
                'user' => $tempUser, // Passar user temporário para os serviços
            ];

            // Criar descrição
            $description = "Checkout: {$checkout->product->name}";
            if ($discountPercentage > 0) {
                $description .= " (Desconto: {$discountPercentage}%)";
            }

            // Criar transação na adquirente
            $sale = null;
            if ($acquirerSlug === 'liberpay') {
                $sale = $this->liberpayService->createSale($totalAmount, $description, $metadata);
            } elseif ($acquirerSlug === 'fullpix') {
                $sale = $this->fullPixService->createTransaction($totalAmount, $description, $metadata);
            }

            if (!$sale) {
                Log::error('Checkout: Falha ao criar transação', [
                    'checkout_id' => $checkout->id,
                    'acquirer' => $acquirerSlug,
                    'amount' => $totalAmount,
                ]);

                return back()->withErrors(['error' => 'Não foi possível processar o pagamento. Verifique os dados e tente novamente.']);
            }

            // Extrair dados da resposta
            $saleId = $sale['id'] ?? $sale['transaction_id'] ?? $sale['sale_id'] ?? null;
            $pixQrCode = null;
            $pixQrCodeImage = null;
            $expiresAt = null;

            // Mapear QR Code baseado na estrutura da API
            if (isset($sale['pix'])) {
                $pixData = $sale['pix'];
                $pixQrCode = $pixData['qrcode'] ?? $pixData['qr_code'] ?? $pixData['code'] ?? $pixData['content'] ?? null;
                $pixQrCodeImage = $pixData['qr_code_image'] ?? $pixData['image'] ?? $pixData['image_base64'] ?? null;
                
                if (isset($pixData['expirationDate'])) {
                    $expiresAt = \Carbon\Carbon::parse($pixData['expirationDate']);
                } elseif (isset($pixData['expires_at'])) {
                    $expiresAt = \Carbon\Carbon::parse($pixData['expires_at']);
                } elseif (isset($pixData['expiration_date'])) {
                    $expiresAt = \Carbon\Carbon::parse($pixData['expiration_date']);
                }
            } elseif (isset($sale['qr_code'])) {
                $pixQrCode = $sale['qr_code'];
                $pixQrCodeImage = $sale['qr_code_image'] ?? null;
            } else {
                $pixQrCode = $sale['pix_code'] ?? $sale['qr_code_content'] ?? $sale['code'] ?? null;
            }

            // Extrair data de expiração geral se não encontrada no PIX
            if (!$expiresAt) {
                if (isset($sale['expires_at'])) {
                    $expiresAt = \Carbon\Carbon::parse($sale['expires_at']);
                } elseif (isset($sale['expiration_date'])) {
                    $expiresAt = \Carbon\Carbon::parse($sale['expiration_date']);
                } else {
                    // Padrão: 30 minutos
                    $expiresAt = now()->addMinutes(30);
                }
            }

            // Garantir que o valor salvo seja EXATAMENTE o valor calculado
            $amountToSave = (float) number_format($totalAmount, 2, '.', '');

            // Salvar a transação no banco de dados
            $saleModel = null;
            if ($acquirerSlug === 'liberpay') {
                $saleModel = LiberpaySale::create([
                    'user_id' => $checkout->user_id,
                    'liberpay_sale_id' => $saleId,
                    'reference_code' => $sale['reference_code'] ?? $sale['reference'] ?? null,
                    'external_reference' => $sale['external_reference'] ?? null,
                    'amount' => $amountToSave,
                    'currency' => $sale['currency'] ?? 'BRL',
                    'status' => 'pending',
                    'pix_qr_code' => $pixQrCode,
                    'pix_qr_code_image' => $pixQrCodeImage,
                    'expires_at' => $expiresAt,
                    'metadata' => $metadata,
                    'liberpay_response' => $sale,
                ]);
            } elseif ($acquirerSlug === 'fullpix') {
                $status = $sale['status'] ?? 'waiting_payment';
                $statusMap = [
                    'waiting_payment' => 'waiting_payment',
                    'paid' => 'paid',
                    'pending' => 'pending',
                ];
                $mappedStatus = $statusMap[strtolower($status)] ?? 'waiting_payment';

                $saleModel = FullPixSale::create([
                    'user_id' => $checkout->user_id,
                    'fullpix_transaction_id' => $saleId,
                    'reference_code' => $sale['id'] ?? null,
                    'external_reference' => $sale['externalRef'] ?? $sale['external_reference'] ?? null,
                    'amount' => $amountToSave,
                    'currency' => $sale['currency'] ?? 'BRL',
                    'status' => $mappedStatus,
                    'pix_qrcode' => $pixQrCode,
                    'pix_qrcode_image' => $pixQrCodeImage,
                    'expires_at' => $expiresAt,
                    'metadata' => $metadata,
                    'fullpix_response' => $sale,
                ]);
                
                // Para FullPix, usar pix_qrcode ao invés de pix_qr_code
                $pixQrCode = $saleModel->pix_qrcode;
                $pixQrCodeImage = $saleModel->pix_qrcode_image;
            }

            if (!$saleModel) {
                Log::error('Checkout: Falha ao salvar transação no banco', [
                    'checkout_id' => $checkout->id,
                    'sale_id' => $saleId,
                    'acquirer' => $acquirerSlug,
                ]);
                return back()->withErrors(['error' => 'Não foi possível finalizar o pagamento. Tente novamente.']);
            }

            // Garantir que temos o código PIX (usar o código salvo no banco)
            $finalPixCode = $pixQrCode;
            if ($acquirerSlug === 'liberpay') {
                $finalPixCode = $saleModel->pix_qr_code;
            } elseif ($acquirerSlug === 'fullpix') {
                $finalPixCode = $saleModel->pix_qrcode;
            }
            
            if (!$finalPixCode) {
                Log::error('Checkout: Código PIX não gerado', [
                    'checkout_id' => $checkout->id,
                    'sale_id' => $saleId,
                    'acquirer' => $acquirerSlug,
                    'pix_qr_code_from_response' => $pixQrCode,
                ]);
                return back()->withErrors(['error' => 'Erro ao gerar código PIX. Tente novamente.']);
            }
            
            // IMPORTANTE: Os dados do formulário já estão salvos no metadata da sale
            // Eles serão preservados quando o pagamento for confirmado via webhook
            
            // Redirecionar para rota de pagamento PIX com o código
            return redirect()->route('payment.pix', [
                'paymentcode' => $finalPixCode
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erros de validação - mostrar mensagens específicas
            return back()->withErrors($e->errors());
        } catch (\Throwable $th) {
            Log::error('Checkout: Erro ao processar pagamento', [
                'checkout_id' => $checkout->id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            // Não mostrar detalhes técnicos ao lead
            return back()->withErrors(['error' => 'Ocorreu um erro ao processar seu pagamento. Por favor, tente novamente ou entre em contato com o suporte.']);
        }
    }
}

