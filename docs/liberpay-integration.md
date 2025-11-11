# Integração Liberpay - Documentação

## URLs da Documentação Oficial

- [Primeiros Passos](https://app.liberpay.pro/docs/intro/first-steps)
- [Formato de Postbacks](https://app.liberpay.pro/docs/intro/postbacks-format)
- [Criar Venda](https://app.liberpay.pro/docs/sales/create-sale)
- [Buscar Venda](https://app.liberpay.pro/docs/sales/find-sale)
- [Listar Vendas](https://app.liberpay.pro/docs/sales/list-sales)
- [Reembolsar Venda](https://app.liberpay.pro/docs/sales/refund-sale)
- [Obter Saldo](https://app.liberpay.pro/docs/balance/get-balance)

## Configuração

### Credenciais Necessárias

1. **Chave Pública** - Obrigatória
2. **Chave Privada** - Obrigatória  
3. **Chave de Saque Externo** - Opcional

### Autenticação

A API da Liberpay utiliza autenticação via header `X-API-Key` com a chave pública.

**IMPORTANTE**: Se receber erro 404, a URL da API pode estar incorreta. Verifique na documentação oficial qual é a URL base correta e configure no `.env`:

```
LIBERPAY_API_URL=https://app.liberpay.pro/api/v1
```

Ou tente outras variações:
- `https://app.liberpay.pro/api/v1`
- `https://api.liberpay.pro/v1`
- `https://api.liberpay.pro/api/v1`

### Problema de SSL no Windows

Se você estiver recebendo erro de certificado SSL, adicione no arquivo `.env`:

```
LIBERPAY_VERIFY_SSL=false
```

**ATENÇÃO**: Use apenas em desenvolvimento. Em produção, mantenha `LIBERPAY_VERIFY_SSL=true` ou remova a linha.

## Estrutura da Resposta da API

A resposta da API pode variar. O código tenta mapear diferentes formatos possíveis:

- `sale.pix.qr_code` ou `sale.pix.content`
- `sale.qr_code`
- `sale.pix_code` ou `sale.qr_code_content`

Se a estrutura for diferente, verifique os logs em `storage/logs/laravel.log` para ver a resposta completa da API.

## Webhook

Configure o webhook na dashboard da Liberpay para:
- URL: `https://seudominio.com/webhook/liberpay`
- Eventos: Todas as mudanças de status de venda

