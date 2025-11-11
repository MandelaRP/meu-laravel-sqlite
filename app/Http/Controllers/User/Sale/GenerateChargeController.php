<?php

declare(strict_types = 1);

namespace App\Http\Controllers\User\Sale;

use App\Http\Controllers\Controller;
use App\Models\Acquirer;
use App\Models\LiberpaySale;
use App\Models\FullPixSale;
use App\Services\LiberpayService;
use App\Services\FullPixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GenerateChargeController extends Controller
{
    public function __construct(
        private readonly LiberpayService $liberpayService,
        private readonly FullPixService $fullPixService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Valor inválido',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Garantir precisão decimal - usar string e converter com precisão
        $amountInput = $request->input('amount');
        // Converter para string primeiro para manter precisão, depois para float
        $amount = (float) number_format((float) $amountInput, 2, '.', '');
        $user = Auth::user();

        // Determinar qual adquirente usar (prioridade: preferência do cliente > configuração global)
        $acquirer = $this->determineAcquirer($user);
        
        if (!$acquirer) {
            return response()->json([
                'message' => 'Nenhuma adquirente ativa para processar esta transação.',
                'status' => 'error',
            ], 400);
        }

        // Determinar qual serviço usar baseado no slug da adquirente
        $acquirerSlug = $acquirer->slug;
        
        if ($acquirerSlug === 'liberpay') {
            if (!$this->liberpayService->isConfigured()) {
                return response()->json([
                    'message' => 'API da Liberpay não está configurada',
                    'status' => 'error',
                ], 400);
            }
        } elseif ($acquirerSlug === 'fullpix') {
            if (!$this->fullPixService->isConfigured()) {
                return response()->json([
                    'message' => 'API da FullPix não está configurada',
                    'status' => 'error',
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Adquirente não suportada',
                'status' => 'error',
            ], 400);
        }

        // Preparar metadados com dados do usuário
        $metadata = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user' => $user, // Passar objeto user completo para o serviço
            // product_id será adicionado se vier do checkout
            'product_id' => $request->input('product_id'),
            'checkout_product_id' => $request->input('product_id'), // Alias para compatibilidade
        ];

        $description = "Depósito de R$ " . number_format((float) $amount, 2, ',', '.') . " - Usuário #{$user->id}";
        
        // Criar transação na adquirente ativa
        $sale = null;
        if ($acquirerSlug === 'liberpay') {
            $sale = $this->liberpayService->createSale((float) $amount, $description, $metadata);
        } elseif ($acquirerSlug === 'fullpix') {
            $sale = $this->fullPixService->createTransaction((float) $amount, $description, $metadata);
        }

        if (!$sale) {
            Log::error("{$acquirer->name}: Falha ao criar transação - resposta vazia ou inválida");
            
            $errorMessage = 'Erro ao gerar cobrança. Verifique se a adquirente está configurada corretamente e se as chaves estão válidas.';
            
            return response()->json([
                'message' => $errorMessage,
                'status' => 'error',
            ], 500);
        }

        // Extrair dados da resposta baseado na adquirente
        $saleId = $sale['id'] ?? $sale['transaction_id'] ?? $sale['sale_id'] ?? null;
        $pixQrCode = null;
        $pixQrCodeImage = null;
        $expiresAt = null;

        // Mapear QR Code baseado na estrutura da API
        if (isset($sale['pix'])) {
            $pixData = $sale['pix'];
            // FullPix usa 'qrcode', Liberpay também pode usar 'qrcode'
            $pixQrCode = $pixData['qrcode'] ?? $pixData['qr_code'] ?? $pixData['code'] ?? $pixData['content'] ?? null;
            $pixQrCodeImage = $pixData['qr_code_image'] ?? $pixData['image'] ?? $pixData['image_base64'] ?? null;
            
            // Extrair data de expiração do PIX
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
            }
        }

        // Garantir que o valor salvo seja EXATAMENTE o valor enviado pelo usuário
        $amountToSave = (float) number_format($amount, 2, '.', '');
        
        // Salvar a transação no banco de dados baseado na adquirente
        if ($acquirerSlug === 'liberpay') {
            Log::debug('Liberpay: Valor sendo salvo no banco', [
                'valor_original' => $amountInput,
                'valor_formatado' => $amount,
                'valor_salvo' => $amountToSave,
            ]);
            
            $liberpaySale = LiberpaySale::create([
                'user_id' => $user->id,
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
            Log::debug('FullPix: Valor sendo salvo no banco', [
                'valor_original' => $amountInput,
                'valor_formatado' => $amount,
                'valor_salvo' => $amountToSave,
            ]);
            
            // Mapear status da FullPix (waiting_payment é o padrão)
            $status = $sale['status'] ?? 'waiting_payment';
            $statusMap = [
                'waiting_payment' => 'waiting_payment',
                'paid' => 'paid',
                'pending' => 'pending',
                'refused' => 'refused',
                'cancelled' => 'cancelled',
                'refunded' => 'refunded',
                'expired' => 'expired',
            ];
            $mappedStatus = $statusMap[strtolower($status)] ?? 'waiting_payment';
            
            $fullpixSale = FullPixSale::create([
                'user_id' => $user->id,
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
        }

        // Mapear resposta da API para o formato esperado pelo frontend
        $response = [
            'message' => 'Transação gerada com sucesso',
            'status' => 'success',
            'sale_id' => $saleId,
        ];

        // Mapear QR Code
        if (isset($sale['pix'])) {
            $pixData = $sale['pix'];
            $response['qrcode'] = [
                'reference_code' => $saleId,
                'external_reference' => $sale['externalRef'] ?? $sale['external_reference'] ?? null,
                'content' => $pixData['qrcode'] ?? $pixData['qr_code'] ?? $pixData['code'] ?? $pixData['content'] ?? null,
                'image_base64' => $pixData['qr_code_image'] ?? $pixData['image'] ?? $pixData['image_base64'] ?? null,
            ];
        } elseif (isset($sale['qr_code'])) {
            $response['qrcode'] = [
                'reference_code' => $saleId,
                'external_reference' => $sale['externalRef'] ?? $sale['external_reference'] ?? null,
                'content' => $sale['qr_code'],
                'image_base64' => $sale['qr_code_image'] ?? null,
            ];
        } else {
            $response['qrcode'] = [
                'reference_code' => $saleId,
                'external_reference' => $sale['externalRef'] ?? $sale['external_reference'] ?? null,
                'content' => $pixQrCode,
                'image_base64' => null,
            ];
        }

        return response()->json($response);
    }

    /**
     * Determina qual adquirente usar baseado na preferência do cliente e configuração global
     * 
     * Prioridade:
     * 1. Se o cliente tem adquirente preferida configurada → usa essa (mesmo que desativada globalmente)
     * 2. Caso contrário, usa a adquirente ativa globalmente
     * 
     * @param \App\Models\User $user
     * @return \App\Models\Acquirer|null
     */
    private function determineAcquirer(\App\Models\User $user): ?\App\Models\Acquirer
    {
        // 1. Verificar se o cliente tem adquirente preferida configurada
        if ($user->preferred_acquirer) {
            $preferredAcquirer = Acquirer::where('slug', $user->preferred_acquirer)->first();
            
            if ($preferredAcquirer) {
                // Verificar se a adquirente preferida está configurada (tem credenciais)
                $isConfigured = false;
                if ($preferredAcquirer->slug === 'liberpay') {
                    $isConfigured = $this->liberpayService->isConfigured();
                } elseif ($preferredAcquirer->slug === 'fullpix') {
                    $isConfigured = $this->fullPixService->isConfigured();
                }
                
                if ($isConfigured) {
                    Log::info('Usando adquirente preferida do cliente', [
                        'user_id' => $user->id,
                        'preferred_acquirer' => $user->preferred_acquirer,
                    ]);
                    return $preferredAcquirer;
                } else {
                    Log::warning('Adquirente preferida do cliente não está configurada, usando padrão global', [
                        'user_id' => $user->id,
                        'preferred_acquirer' => $user->preferred_acquirer,
                    ]);
                }
            }
        }
        
        // 2. Usar configuração global (adquirente ativa)
        $globalAcquirer = Acquirer::where('is_active', true)->first();
        
        if ($globalAcquirer) {
            Log::info('Usando adquirente ativa globalmente', [
                'user_id' => $user->id,
                'global_acquirer' => $globalAcquirer->slug,
            ]);
            return $globalAcquirer;
        }
        
        // 3. Nenhuma adquirente disponível
        Log::error('Nenhuma adquirente ativa disponível', [
            'user_id' => $user->id,
            'preferred_acquirer' => $user->preferred_acquirer,
        ]);
        
        return null;
    }
}
