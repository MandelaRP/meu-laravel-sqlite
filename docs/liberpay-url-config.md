# Configuração da URL da API Liberpay

## Problema: Erro 404

Se você está recebendo erro 404 ao verificar o status da API, significa que a URL base está incorreta.

## Como encontrar a URL correta

1. Acesse a documentação oficial: https://app.liberpay.pro/docs/intro/first-steps
2. Procure por "Base URL" ou "API Endpoint" ou "URL da API"
3. Procure por exemplos de requisições (geralmente mostram a URL completa)

## URLs comuns para testar

Adicione uma dessas URLs no arquivo `.env` e teste:

```env
# Opção 1
LIBERPAY_API_URL=https://app.liberpay.pro/api/v1

# Opção 2 (sem /api)
LIBERPAY_API_URL=https://app.liberpay.pro/v1

# Opção 3 (subdomínio api)
LIBERPAY_API_URL=https://api.liberpay.pro/v1

# Opção 4
LIBERPAY_API_URL=https://api.liberpay.pro/api/v1

# Opção 5 (sem subdomínio)
LIBERPAY_API_URL=https://liberpay.pro/api/v1
```

## Como testar

1. Adicione a URL no `.env`
2. Execute: `php artisan config:clear`
3. Tente verificar o status novamente em `/admin/acquirers`
4. Verifique os logs em `storage/logs/laravel.log`

## Exemplo na documentação

Geralmente a documentação mostra exemplos como:

```bash
curl https://[URL_BASE]/balance \
  -H "X-API-Key: sua-chave-publica"
```

Use a URL base mostrada no exemplo da documentação.

