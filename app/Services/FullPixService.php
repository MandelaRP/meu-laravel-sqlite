<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Acquirer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FullPixService
{
    private ?string $secretKey = null;
    private ?string $publicKey = null;
    private string $baseUrl;

    public function __construct()
    {
        $acquirer = Acquirer::where('slug', 'fullpix')->first();
        
        if ($acquirer && $acquirer->credentials) {
            $this->secretKey = $acquirer->credentials['secret_key'] ?? null;
            $this->publicKey = $acquirer->credentials['public_key'] ?? null;
        }

        // URL base da API FullPix
        // Documentação: https://api.fullpix.com.br/functions/v1
        $this->baseUrl = env('FULLPIX_API_URL', 'https://api.fullpix.com.br/functions/v1');
    }

    /**
     * Verifica se a API está configurada
     */
    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->publicKey);
    }

    /**
     * Prepara headers de autenticação
     * Baseado na documentação: Basic Auth com Secret Key:Public Key
     * Username: Secret Key
     * Password: Public Key
     */
    private function getAuthHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Basic Auth: username:password (secret_key:public_key)
        if (!empty($this->secretKey) && !empty($this->publicKey)) {
            $credentials = base64_encode($this->secretKey . ':' . $this->publicKey);
            $headers['Authorization'] = 'Basic ' . $credentials;
        }

        return $headers;
    }

    /**
     * Prepara cliente HTTP com configurações
     */
    private function getHttpClient()
    {
        $options = [
            'timeout' => 10,
        ];

        if (env('FULLPIX_VERIFY_SSL', true) === false || env('APP_ENV') === 'local') {
            $options['verify'] = false;
        }

        return Http::withOptions($options);
    }

    /**
     * Cria uma nova transação PIX
     * 
     * @param float $amount Valor da transação em reais
     * @param string $description Descrição da transação
     * @param array $metadata Metadados adicionais (deve incluir 'user' com dados do usuário)
     * @return array|null
     */
    public function createTransaction(float $amount, string $description = '', array $metadata = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('FullPix: API não configurada');
            return null;
        }

        try {
            $endpoint = "{$this->baseUrl}/transactions";
            
            // Converter amount para centavos (int)
            $amountInCents = (int) bcmul((string) $amount, '100', 0);
            
            // Obter dados do usuário dos metadados
            $user = $metadata['user'] ?? null;
            
            // Preparar customer (obrigatório)
            $customer = [
                'name' => $user?->name ?? 'Cliente Luckpay',
                'email' => $user?->email ?? 'cliente@luckpay.com',
            ];
            
            // Adicionar telefone se disponível (formato: apenas números, DDD + número)
            if (!empty($user?->phone)) {
                $phone = preg_replace('/[^0-9]/', '', $user->phone);
                if (strlen($phone) >= 10 && strlen($phone) <= 11) {
                    $customer['phone'] = $phone;
                }
            } else {
                // Telefone padrão válido (11 dígitos)
                $customer['phone'] = '11999999999';
            }
            
            // Adicionar documento se disponível
            if (!empty($user?->document)) {
                $document = preg_replace('/[^0-9]/', '', $user->document);
                if (strlen($document) >= 11) {
                    // Determinar tipo: CPF (11 dígitos) ou CNPJ (14 dígitos)
                    $documentType = strlen($document) === 11 ? 'CPF' : 'CNPJ';
                    $customer['document'] = [
                        'type' => $documentType,
                        'number' => $document,
                    ];
                }
            } else {
                // CPF padrão válido para testes
                $customer['document'] = [
                    'type' => 'CPF',
                    'number' => '12345678901',
                ];
            }
            
            // Preparar items (obrigatório, pelo menos 1 item)
            $items = [
                [
                    'title' => $description ?: 'Depósito',
                    'unitPrice' => $amountInCents, // em centavos
                    'quantity' => 1,
                    'tangible' => false, // Produto digital
                ]
            ];
            
            // Adicionar externalRef se disponível
            $externalRef = null;
            if (isset($metadata['user_id'])) {
                $externalRef = 'LUCKPAY-' . $metadata['user_id'] . '-' . time();
            }
            
            // Adicionar postbackUrl para webhooks
            $appUrl = env('APP_URL', 'http://localhost:8000');
            $postbackUrl = rtrim($appUrl, '/') . '/webhook/fullpix';
            
            // Montar payload conforme documentação FullPix
            $payload = [
                'paymentMethod' => 'PIX',
                'amount' => $amountInCents, // em centavos
                'customer' => $customer,
                'items' => $items,
                'postbackUrl' => $postbackUrl,
            ];
            
            // Adicionar externalRef se disponível
            if ($externalRef) {
                $items[0]['externalRef'] = $externalRef;
            }
            
            // Adicionar description se disponível
            if ($description) {
                $payload['description'] = $description;
            }
            
            // Adicionar metadata se não estiver vazio
            if (!empty($metadata)) {
                $metadataToSend = $metadata;
                unset($metadataToSend['user']);
                if (!empty($metadataToSend)) {
                    $payload['metadata'] = $metadataToSend;
                }
            }
            
            // Log do payload completo para debug
            Log::debug('FullPix: Payload completo', [
                'payload' => $payload,
                'payload_json' => json_encode($payload, JSON_PRETTY_PRINT),
            ]);
            
            $authHeaders = $this->getAuthHeaders();
            
            Log::debug('FullPix: Criando transação', [
                'url' => $endpoint,
                'payload' => $payload,
                'auth_header' => isset($authHeaders['Authorization']) ? substr($authHeaders['Authorization'], 0, 30) . '...' : 'NÃO DEFINIDO',
            ]);
            
            $response = $this->getHttpClient()
                ->withHeaders($authHeaders)
                ->post($endpoint, $payload);

            $status = $response->status();
            $body = $response->body();
            $headers = $response->headers();
            $data = $response->json();
            
            // Log completo da resposta para debug
            Log::debug('FullPix: Resposta da API', [
                'status' => $status,
                'headers' => $headers,
                'body_length' => strlen($body),
                'is_json' => $data !== null,
                'body_preview' => substr($body, 0, 200),
            ]);
            
            // Verificar se a resposta é HTML (erro de autenticação ou endpoint errado)
            if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
                Log::error('FullPix: API retornou HTML ao invés de JSON', [
                    'status' => $status,
                    'body_preview' => substr($body, 0, 500),
                    'url' => $endpoint,
                ]);
                return null;
            }
            
            if ($response->successful()) {
                Log::info('FullPix: Transação criada com sucesso', [
                    'status' => $status,
                    'response' => $data,
                ]);
                
                if (empty($data)) {
                    Log::error('FullPix: Resposta vazia da API');
                    return null;
                }
                
                return $data;
            }

            $errorBody = $response->body();
            $errorJson = $response->json();
            
            Log::error('FullPix: Erro ao criar transação', [
                'status' => $response->status(),
                'response' => $errorBody,
                'json' => $errorJson,
                'url' => $endpoint,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FullPix: Exceção ao criar transação', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (str_contains($e->getMessage(), 'SSL certificate')) {
                Log::warning('FullPix: Erro de certificado SSL. Adicione FULLPIX_VERIFY_SSL=false no .env para desenvolvimento');
            }

            return null;
        }
    }

    /**
     * Busca uma transação específica
     * 
     * @param string $transactionId ID da transação
     * @return array|null
     */
    public function findTransaction(string $transactionId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->getHttpClient()
                ->withHeaders($this->getAuthHeaders())
                ->get("{$this->baseUrl}/transactions/{$transactionId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('FullPix: Erro ao buscar transação', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verifica o status da API
     * 
     * @return string 'online'|'offline'|'error'
     */
    public function checkApiStatus(): string
    {
        if (!$this->isConfigured()) {
            Log::warning('FullPix: API não configurada para verificação de status');
            return 'offline';
        }

        try {
            // Tentar endpoint de transações para verificar autenticação
            $endpoint = "{$this->baseUrl}/transactions";
            
            $response = $this->getHttpClient()
                ->timeout(10)
                ->withHeaders($this->getAuthHeaders())
                ->get($endpoint);
            
            $status = $response->status();
            $body = $response->body();
            
            // Se retornar 200, a API está online e autenticada
            if ($status === 200 && $response->successful()) {
                Log::info('FullPix: API está online', ['status' => $status]);
                return 'online';
            }
            
            // Se retornar HTML, problema de autenticação ou endpoint
            if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
                Log::warning('FullPix: API retornou HTML - problema de autenticação ou endpoint');
                return 'error';
            }
            
            if (in_array($status, [401, 403])) {
                Log::warning('FullPix: Erro de autenticação', ['status' => $status]);
                return 'error';
            }

            return 'offline';
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            Log::error('FullPix: Exceção ao verificar status', [
                'message' => $errorMessage,
                'code' => $e->getCode(),
            ]);

            if (str_contains($errorMessage, 'SSL certificate')) {
                Log::warning('FullPix: Erro de certificado SSL. Adicione FULLPIX_VERIFY_SSL=false no .env para desenvolvimento');
                return 'error';
            }

            return 'error';
        }
    }

    /**
     * Cria um saque via PIX
     * 
     * @param float $amount Valor do saque em reais
     * @param string $pixKeyType Tipo da chave PIX (CPF, CNPJ, EMAIL, PHONE, EVP)
     * @param string $pixKey Valor da chave PIX
     * @param string|null $description Descrição do saque
     * @param string|null $postbackUrl URL para receber webhooks
     * @return array|null
     */
    public function createWithdrawal(float $amount, string $pixKeyType, string $pixKey, ?string $description = null, ?string $postbackUrl = null): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('FullPix: API não configurada para saque');
            return null;
        }

        try {
            $endpoint = "{$this->baseUrl}/withdrawals/cashout";
            
            // Converter amount para centavos (int)
            $amountInCents = (int) bcmul((string) $amount, '100', 0);
            
            // Normalizar tipo da chave PIX para o formato da API
            $pixKeyTypeNormalized = strtoupper($pixKeyType);
            
            // Normalizar chave PIX baseado no tipo
            $pixKeyNormalized = $this->normalizePixKey($pixKeyTypeNormalized, $pixKey);
            
            if (!$pixKeyNormalized) {
                Log::error('FullPix: Chave PIX inválida', [
                    'type' => $pixKeyTypeNormalized,
                    'key' => substr($pixKey, 0, 10) . '...',
                ]);
                return null;
            }
            
            // Adicionar postbackUrl para webhooks se não fornecido
            if (!$postbackUrl) {
                $appUrl = env('APP_URL', 'http://localhost:8000');
                $postbackUrl = rtrim($appUrl, '/') . '/webhook/fullpix';
            }
            
            // Gerar Idempotency-Key (UUID v4)
            $idempotencyKey = \Illuminate\Support\Str::uuid()->toString();
            
            // Montar payload conforme documentação FullPix
            $payload = [
                'pixkeytype' => $pixKeyTypeNormalized,
                'pixkey' => $pixKeyNormalized,
                'requestedamount' => $amountInCents, // em centavos
                'description' => $description ?? 'Saque via LuckPay',
                'isPix' => true,
                'postbackUrl' => $postbackUrl,
            ];
            
            $authHeaders = $this->getAuthHeaders();
            $authHeaders['Idempotency-Key'] = $idempotencyKey;
            
            Log::debug('FullPix: Criando saque', [
                'url' => $endpoint,
                'payload' => $payload,
                'idempotency_key' => $idempotencyKey,
            ]);
            
            $response = $this->getHttpClient()
                ->withHeaders($authHeaders)
                ->post($endpoint, $payload);

            $status = $response->status();
            $body = $response->body();
            $data = $response->json();
            
            if ($response->successful()) {
                Log::info('FullPix: Saque criado com sucesso', [
                    'status' => $status,
                    'response' => $data,
                ]);
                
                return $data;
            }

            $errorBody = $response->body();
            $errorJson = $response->json();
            
            Log::error('FullPix: Erro ao criar saque', [
                'status' => $status,
                'response' => $errorBody,
                'json' => $errorJson,
                'url' => $endpoint,
            ]);

            // Retornar null para indicar erro, mas incluir informações do erro
            // O controller tratará isso criando o saque como pending
            return null;
        } catch (\Exception $e) {
            Log::error('FullPix: Exceção ao criar saque', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Normaliza a chave PIX baseado no tipo
     */
    private function normalizePixKey(string $type, string $key): ?string
    {
        return match($type) {
            'CPF' => preg_replace('/[^0-9]/', '', $key), // Apenas números, 11 dígitos
            'CNPJ' => preg_replace('/[^0-9]/', '', $key), // Apenas números, 14 dígitos
            'EMAIL' => strtolower(trim($key)), // Email em minúsculas
            'PHONE' => $this->normalizePhone($key), // Telefone com código do país
            'EVP' => $key, // UUID já está no formato correto
            default => null,
        };
    }

    /**
     * Normaliza telefone para formato +55XXXXXXXXXXX
     */
    private function normalizePhone(string $phone): string
    {
        // Remove tudo exceto números
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Se já começa com 55, adiciona +
        if (str_starts_with($cleaned, '55')) {
            return '+' . $cleaned;
        }
        
        // Se tem 10 ou 11 dígitos, adiciona +55
        if (strlen($cleaned) >= 10 && strlen($cleaned) <= 11) {
            return '+55' . $cleaned;
        }
        
        return $phone;
    }
}

