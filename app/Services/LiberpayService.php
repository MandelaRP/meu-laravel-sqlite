<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Acquirer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiberpayService
{
    private ?string $chavePublica = null;
    private ?string $chavePrivada = null;
    private ?string $chaveSaqueExterno = null;
    private string $baseUrl;

    public function __construct()
    {
        $acquirer = Acquirer::where('slug', 'liberpay')->first();
        
        if ($acquirer && $acquirer->credentials) {
            $this->chavePublica = $acquirer->credentials['chave_publica'] ?? null;
            $this->chavePrivada = $acquirer->credentials['chave_privada'] ?? null;
            $this->chaveSaqueExterno = $acquirer->credentials['chave_saque_externo'] ?? null;
        }

        // URL base da API
        // Documentação: https://app.liberpay.pro/docs/sales/create-sale
        // Endpoint correto: https://api.liberpay.pro/v1
        $this->baseUrl = env('LIBERPAY_API_URL', 'https://api.liberpay.pro/v1');
    }

    /**
     * Verifica se a API está configurada
     */
    public function isConfigured(): bool
    {
        return !empty($this->chavePublica) && !empty($this->chavePrivada);
    }

    /**
     * Prepara headers de autenticação
     * Baseado na documentação: https://app.liberpay.pro/docs/sales/create-sale
     * Autenticação: Basic Auth com chave pública:chave privada
     */
    private function getAuthHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Basic Auth: username:password (chave pública:chave privada)
        if (!empty($this->chavePublica) && !empty($this->chavePrivada)) {
            $credentials = base64_encode($this->chavePublica . ':' . $this->chavePrivada);
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
            'timeout' => 10, // Reduzido para 10 segundos para evitar travamento
        ];

        // Em desenvolvimento Windows, pode ser necessário desabilitar verificação SSL
        // Configure LIBERPAY_VERIFY_SSL=false no .env apenas em desenvolvimento
        if (env('LIBERPAY_VERIFY_SSL', true) === false || env('APP_ENV') === 'local') {
            $options['verify'] = false;
        }

        return Http::withOptions($options);
    }

    /**
     * Cria uma nova venda PIX
     * 
     * @param float $amount Valor da transação em reais
     * @param string $description Descrição da venda
     * @param array $metadata Metadados adicionais (deve incluir 'user' com dados do usuário)
     * @return array|null
     */
    public function createSale(float $amount, string $description = '', array $metadata = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('Liberpay: API não configurada');
            return null;
        }

        try {
            // Baseado na documentação: https://app.liberpay.pro/docs/sales/create-sale
            // Tentar primeiro /transactions, se não funcionar, tentar /sales
            $endpoint = "{$this->baseUrl}/transactions";
            
            // Verificar se há um endpoint alternativo configurado
            $alternativeEndpoint = env('LIBERPAY_SALES_ENDPOINT', null);
            if ($alternativeEndpoint) {
                $endpoint = "{$this->baseUrl}/{$alternativeEndpoint}";
            }
            
            // Converter amount para centavos (int32)
            $amountInCents = (int) round($amount * 100);
            
            // Obter dados do usuário dos metadados
            $user = $metadata['user'] ?? null;
            
            // Preparar customer (obrigatório)
            // Se não tiver usuário, usar dados padrão válidos
            $customer = [
                'name' => $user?->name ?? 'Cliente Luckpay',
                'email' => $user?->email ?? 'cliente@luckpay.com',
            ];
            
            // Adicionar telefone se disponível (formato: apenas números)
            if (!empty($user?->phone)) {
                $phone = preg_replace('/[^0-9]/', '', $user->phone);
                if (strlen($phone) >= 10) {
                    $customer['phone'] = $phone;
                }
            } else {
                // Telefone padrão válido (11 dígitos)
                $customer['phone'] = '11999999999';
            }
            
            // Adicionar documento se disponível
            // A API espera objeto com 'type' e 'number'
            if (!empty($user?->document)) {
                $document = preg_replace('/[^0-9]/', '', $user->document);
                if (strlen($document) >= 11) {
                    // Determinar tipo: CPF (11 dígitos) ou CNPJ (14 dígitos)
                    $documentType = strlen($document) === 11 ? 'cpf' : 'cnpj';
                    $customer['document'] = [
                        'type' => $documentType,
                        'number' => $document,
                    ];
                }
            } else {
                // CPF padrão válido para testes
                $customer['document'] = [
                    'type' => 'cpf',
                    'number' => '12345678901',
                ];
            }
            
            // Preparar items (obrigatório, pelo menos 1 item)
            // A API espera camelCase e campo 'tangible' obrigatório
            // Verificar documentação: pode precisar de 'description' também
            $items = [
                [
                    'title' => $description ?: 'Depósito',
                    'description' => $description ?: 'Depósito via PIX',
                    'quantity' => 1,
                    'unitPrice' => $amountInCents, // em centavos (camelCase)
                    'tangible' => false, // Produto digital (não físico)
                ]
            ];
            
            // Preparar objeto pix (expiração em minutos - padrão 30 minutos)
            // A API espera camelCase: expiresIn
            $pix = [
                'expiresIn' => 30, // minutos (camelCase)
            ];
            
            // Montar payload conforme documentação
            $payload = [
                'amount' => $amountInCents, // em centavos (int32)
                'paymentMethod' => 'pix',
                'items' => $items,
                'customer' => $customer,
                'pix' => $pix,
            ];
            
            // Adicionar externalRef se disponível
            if (isset($metadata['user_id'])) {
                $payload['externalRef'] = 'LUCKPAY-' . $metadata['user_id'] . '-' . time();
            }
            
            // Adicionar metadata se não estiver vazio (pode ser string ou objeto)
            if (!empty($metadata)) {
                // Remover 'user' dos metadados antes de enviar (já foi usado)
                $metadataToSend = $metadata;
                unset($metadataToSend['user']);
                if (!empty($metadataToSend)) {
                    $payload['metadata'] = json_encode($metadataToSend);
                }
            }
            
            // Adicionar postbackUrl se configurado
            $appUrl = env('APP_URL', 'http://localhost:8000');
            $postbackUrl = rtrim($appUrl, '/') . '/webhook/liberpay';
            $payload['postbackUrl'] = $postbackUrl;
            
            // Log do payload completo para debug
            Log::debug('Liberpay: Payload completo', [
                'payload' => $payload,
                'payload_json' => json_encode($payload, JSON_PRETTY_PRINT),
            ]);
            
            $authHeaders = $this->getAuthHeaders();
            
            Log::debug('Liberpay: Criando venda', [
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
            Log::debug('Liberpay: Resposta da API', [
                'status' => $status,
                'headers' => $headers,
                'body_length' => strlen($body),
                'is_json' => $data !== null,
                'body_preview' => substr($body, 0, 200),
            ]);
            
            // Verificar se a resposta é HTML (erro de autenticação ou endpoint errado)
            if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
                Log::error('Liberpay: API retornou HTML ao invés de JSON', [
                    'status' => $status,
                    'body_preview' => substr($body, 0, 500),
                    'url' => $endpoint,
                    'headers_received' => $headers,
                    'auth_used' => isset($authHeaders['Authorization']) ? 'Basic Auth configurado' : 'Sem autenticação',
                ]);
                
                // Tentar verificar se é um problema de permissões ou conta
                if (str_contains($body, 'login') || str_contains($body, 'Login') || str_contains($body, 'LiberPay')) {
                    Log::error('Liberpay: A API está redirecionando para página de login. Possíveis causas:');
                    Log::error('  1. A conta precisa ser ativada para criar transações');
                    Log::error('  2. As chaves não têm permissão para criar transações');
                    Log::error('  3. O endpoint requer autenticação diferente');
                    Log::error('  4. A conta está em modo sandbox e precisa ser ativada para produção');
                }
                
                return null;
            }
            
            if ($response->successful()) {
                Log::info('Liberpay: Venda criada com sucesso', [
                    'status' => $status,
                    'response' => $data,
                ]);
                
                if (empty($data)) {
                    Log::error('Liberpay: Resposta vazia da API');
                    return null;
                }
                
                // Se a resposta não tiver dados do PIX, buscar a venda completa
                // O QR code pode estar em um endpoint separado ou na resposta completa
                if (!isset($data['pix']) && isset($data['id'])) {
                    // Tentar buscar a venda completa para obter o QR code
                    $saleDetails = $this->findSale((string) $data['id']);
                    if ($saleDetails && isset($saleDetails['pix'])) {
                        $data['pix'] = $saleDetails['pix'];
                    }
                }
                
                return $data;
            }

            $errorBody = $response->body();
            $errorJson = $response->json();
            
            Log::error('Liberpay: Erro ao criar venda', [
                'status' => $response->status(),
                'response' => $errorBody,
                'json' => $errorJson,
                'url' => $endpoint,
            ]);

            // Erro 424 geralmente significa problema na conta da adquirente
            if ($response->status() === 424) {
                Log::error('Liberpay: Erro 424 - Problema na conta da adquirente. Verifique se:');
                Log::error('  1. A conta está ativada e configurada corretamente');
                Log::error('  2. As chaves têm permissão para criar transações');
                Log::error('  3. Não há pendências na conta');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Liberpay: Exceção ao criar venda', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (str_contains($e->getMessage(), 'SSL certificate')) {
                Log::warning('Liberpay: Erro de certificado SSL. Adicione LIBERPAY_VERIFY_SSL=false no .env para desenvolvimento');
            }

            return null;
        }
    }

    /**
     * Busca uma venda específica
     * 
     * @param string $saleId ID da venda
     * @return array|null
     */
    public function findSale(string $saleId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            // Endpoint para buscar transação: /transactions/{id}
            $response = $this->getHttpClient()
                ->withHeaders($this->getAuthHeaders())
                ->get("{$this->baseUrl}/transactions/{$saleId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Liberpay: Erro ao buscar venda', [
                'sale_id' => $saleId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Lista vendas
     * 
     * @param array $filters Filtros opcionais
     * @return array|null
     */
    public function listSales(array $filters = []): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            // Endpoint para listar transações: /transactions
            $response = $this->getHttpClient()
                ->withHeaders($this->getAuthHeaders())
                ->get("{$this->baseUrl}/transactions", $filters);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Liberpay: Erro ao listar vendas', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Reembolsa uma venda
     * 
     * @param string $saleId ID da venda
     * @param float|null $amount Valor do reembolso (null para reembolso total)
     * @param string $reason Motivo do reembolso
     * @return array|null
     */
    public function refundSale(string $saleId, ?float $amount = null, string $reason = ''): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $data = [
                'reason' => $reason ?: 'Reembolso solicitado',
            ];

            if ($amount !== null) {
                $data['amount'] = $amount;
            }

            // Endpoint para reembolsar: /transactions/{id}/refund
            $response = $this->getHttpClient()
                ->withHeaders($this->getAuthHeaders())
                ->post("{$this->baseUrl}/transactions/{$saleId}/refund", $data);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Liberpay: Erro ao reembolsar venda', [
                'sale_id' => $saleId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Obtém o saldo da conta
     * 
     * @return array|null
     */
    public function getBalance(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            // Baseado na documentação: https://app.liberpay.pro/docs/balance/get-balance
            $response = $this->getHttpClient()
                ->withHeaders($this->getAuthHeaders())
                ->get("{$this->baseUrl}/balance");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Liberpay: Erro ao buscar saldo', [
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
    /**
     * Verifica o status da API
     * Se a API já estiver online, mantém online
     */
    public function checkApiStatus(): string
    {
        if (!$this->isConfigured()) {
            Log::warning('Liberpay: API não configurada para verificação de status');
            return 'offline';
        }

        try {
            // Tentar diferentes endpoints para verificar autenticação
            // Documentação: https://app.liberpay.pro/docs
            // Endpoints possíveis: /balance, /transactions, /sales
            $endpoints = [
                "{$this->baseUrl}/balance",
                "{$this->baseUrl}/transactions",
                "{$this->baseUrl}/sales",
            ];
            
            $response = null;
            $lastError = null;
            
            foreach ($endpoints as $endpoint) {
                try {
                    Log::debug('Liberpay: Tentando endpoint', ['url' => $endpoint]);
                    
                    $response = $this->getHttpClient()
                        ->timeout(10)
                        ->withHeaders($this->getAuthHeaders())
                        ->get($endpoint);
                    
                    $status = $response->status();
                    $body = $response->body();
                    
                    // Se retornar 200, encontramos um endpoint válido
                    if ($status === 200 && $response->successful()) {
                        Log::info('Liberpay: Endpoint válido encontrado', ['url' => $endpoint]);
                        break;
                    }
                    
                    // Se retornar 401/403, as credenciais estão erradas mas o endpoint pode estar certo
                    if (in_array($status, [401, 403])) {
                        Log::warning('Liberpay: Endpoint pode estar correto, mas credenciais inválidas', [
                            'url' => $endpoint,
                            'status' => $status
                        ]);
                        $lastError = 'auth';
                        continue;
                    }
                    
                    // Se retornar HTML, endpoint errado
                    if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
                        Log::debug('Liberpay: Endpoint retornou HTML', ['url' => $endpoint]);
                        continue;
                    }
                    
                } catch (\Exception $e) {
                    Log::debug('Liberpay: Erro ao tentar endpoint', [
                        'url' => $endpoint,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            if (!$response) {
                Log::error('Liberpay: Nenhum endpoint válido encontrado');
                return 'error';
            }

            $status = $response->status();
            $body = $response->body();
            
            // Se retornar 200, a API está online e autenticada
            if ($status === 200 && $response->successful()) {
                Log::info('Liberpay: API está online', ['status' => $status]);
                return 'online';
            }
            
            // Se retornar HTML, problema de autenticação ou endpoint
            if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
                Log::warning('Liberpay: API retornou HTML - problema de autenticação ou endpoint');
                return 'error';
            }
            
            if (in_array($status, [401, 403])) {
                Log::warning('Liberpay: Erro de autenticação', ['status' => $status]);
                return 'error';
            }

            $errorBody = $response->body();
            $errorJson = $response->json();
            
            Log::warning('Liberpay: API retornou erro', [
                'status' => $response->status(),
                'body' => $errorBody,
                'json' => $errorJson,
                'url' => "{$this->baseUrl}/balance",
                'headers_sent' => $this->getAuthHeaders(),
            ]);

            // Se for erro 404, a URL está incorreta
            if ($response->status() === 404) {
                Log::error('Liberpay: URL da API não encontrada (404).');
                Log::error('Liberpay: URL tentada: ' . "{$this->baseUrl}/balance");
                Log::error('Liberpay: Acesse a documentação: https://app.liberpay.pro/docs/intro/first-steps');
                Log::error('Liberpay: Procure pela "Base URL" ou "API Endpoint" na documentação');
                Log::error('Liberpay: Configure LIBERPAY_API_URL no .env com a URL correta');
                Log::error('Liberpay: Exemplos para testar:');
                Log::error('Liberpay:   - LIBERPAY_API_URL=https://app.liberpay.pro/v1');
                Log::error('Liberpay:   - LIBERPAY_API_URL=https://api.liberpay.pro/v1');
                return 'error';
            }

            // Se for erro 401 ou 403, é problema de autenticação
            if (in_array($response->status(), [401, 403])) {
                Log::error('Liberpay: Erro de autenticação. Verifique se as chaves estão corretas.');
                return 'error';
            }

            return 'offline';
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            Log::error('Liberpay: Exceção ao verificar status', [
                'message' => $errorMessage,
                'code' => $e->getCode(),
            ]);

            // Se for erro de SSL, retornar 'error' mas com mensagem específica
            if (str_contains($errorMessage, 'SSL certificate')) {
                Log::warning('Liberpay: Erro de certificado SSL. Adicione LIBERPAY_VERIFY_SSL=false no .env para desenvolvimento');
                return 'error';
            }

            return 'error';
        }
    }
}

